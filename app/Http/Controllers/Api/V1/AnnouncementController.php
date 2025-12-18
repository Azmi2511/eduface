<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Notification;
use App\Models\User;
use App\Http\Resources\Api\V1\AnnouncementResource;
use App\Http\Requests\Api\V1\Announcement\StoreAnnouncementRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    /**
     * List Pengumuman (Admin View)
     */
    public function index()
    {
        $announcements = Announcement::with('recipient')
            ->latest()
            ->paginate(10);

        return AnnouncementResource::collection($announcements);
    }

    /**
     * Store Pengumuman & Blast Notifikasi
     */
    public function store(StoreAnnouncementRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['date_timesend '] = $request->datetime_send;
            $data['recipient_id'] = ($request->recipient === 'specific') ? $request->user_id : null;

            // Handle File Upload menggunakan Storage (Lebih Aman)
            if ($request->hasFile('attachment_file')) {
                $path = $request->file('attachment_file')->store('announcements', 'public');
                $data['attachment_file'] = $path;
            }

            $announcement = Announcement::create($data);

            // Logic Mencari Target User
            $targetUserIds = $this->getTargetUserIds($request);

            // Kirim Notifikasi Bulk
            if (!empty($targetUserIds)) {
                $this->sendBulkNotifications($announcement, $targetUserIds, $request->message);
            }

            DB::commit();
            return (new AnnouncementResource($announcement))
                ->additional(['message' => 'Pengumuman berhasil dibuat.']);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update Pengumuman
     */
    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'message'         => 'required',
            'datetime_send'   => 'required|date',
            'attachment_file' => 'nullable|file|max:2048'
        ]);

        try {
            $data = [
                'message'         => $request->message,
                'sent_at'         => $request->datetime_send,
                'attachment_link' => $request->attachment_link
            ];

            if ($request->hasFile('attachment_file')) {
                // Hapus file lama
                if ($announcement->attachment_file) Storage::disk('public')->delete($announcement->attachment_file);
                
                $data['attachment_file'] = $request->file('attachment_file')->store('announcements', 'public');
            }

            $announcement->update($data);

            // Sync pesan di tabel notifikasi
            Notification::where('ann_id', $announcement->id)
                ->update(['message' => Str::limit($request->message, 50)]);

            return new AnnouncementResource($announcement);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Update gagal.'], 500);
        }
    }

    /**
     * Hapus Pengumuman
     */
    public function destroy(Announcement $announcement)
    {
        if ($announcement->attachment_file) {
            Storage::disk('public')->delete($announcement->attachment_file);
        }
        
        $announcement->delete(); // Notifikasi terhapus jika di DB diset cascade
        return response()->json(['message' => 'Pengumuman dihapus.']);
    }

    /**
     * Get Detail Pengumuman
     */
    public function show(Announcement $announcement)
    {
        return new AnnouncementResource($announcement);
    }

    // --- Private Helper Functions ---

    private function getTargetUserIds($request)
    {
        if ($request->recipient === 'specific') {
            return [$request->user_id];
        }

        $query = User::where('is_active', 1);
        if ($request->recipient !== 'all') {
            $query->where('role', $request->recipient);
        }
        return $query->pluck('id')->toArray();
    }

    private function sendBulkNotifications($announcement, $userIds, $message)
    {
        $now = now();
        $notifications = array_map(fn($id) => [
            'user_id'    => $id,
            'ann_id'     => $announcement->id,
            'message'    => Str::limit($message, 50),
            'link'       => 'announcements/' . $announcement->id,
            'is_read'    => 0,
            'created_at' => $now,
            'updated_at' => $now
        ], $userIds);

        foreach (array_chunk($notifications, 500) as $chunk) {
            Notification::insert($chunk);
        }
    }
}