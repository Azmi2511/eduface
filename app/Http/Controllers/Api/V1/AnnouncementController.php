<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Announcement;
use App\Models\User;
use App\Models\Notification;
use App\Helpers\NotificationHelper;
use App\Http\Resources\AnnouncementResource;

class AnnouncementController extends Controller
{
    // index
    public function index(Request $request)
    {
        $query = Announcement::with('recipient')->orderBy('sent_at', 'desc');

        if ($request->filled('search')) {
            $query->where('message', 'like', '%' . $request->search . '%');
        }

        $announcements = $query->paginate(10);

        $stats = [
            'total' => $announcements->total(),
            'with_attachment' => Announcement::whereNotNull('attachment_file')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => AnnouncementResource::collection($announcements),
            'meta' => [
                'current_page' => $announcements->currentPage(),
                'last_page' => $announcements->lastPage(),
                'per_page' => $announcements->perPage(),
                'total' => $announcements->total(),
                'stats' => $stats
            ]
        ]);
    }

    // store
    public function store(Request $request)
    {
        $request->validate([
            'recipient'       => 'required|in:all,student,parent,teacher,specific',
            'user_id'         => 'required_if:recipient,specific|exists:users,id',
            'message'         => 'required|string',
            'datetime_send'   => 'required|date',
            'attachment_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120',
            'attachment_link' => 'nullable|url'
        ]);

        DB::beginTransaction();

        try {
            $data = [
                'message'         => $request->message,
                'sent_at'         => $request->datetime_send,
                'attachment_link' => $request->attachment_link,
                'recipient'       => $request->recipient,
                'recipient_id'    => $request->recipient === 'specific' ? $request->user_id : null
            ];

            if ($request->hasFile('attachment_file')) {
                $file = $request->file('attachment_file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('uploads');

                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true, true);
                }

                $file->move($destinationPath, $fileName);
                $data['attachment_file'] = $fileName;
            }

            $announcement = Announcement::create($data);

            $targetUsersQuery = User::where('is_active', 1);

            if ($request->recipient === 'specific') {
                $targetUsersQuery->where('id', $request->user_id);
            } elseif ($request->recipient !== 'all') {
                $targetUsersQuery->where('role', $request->recipient);
            }

            $targetUsers = $targetUsersQuery->select('id', 'fcm_token')->get();

            $notificationsData = [];
            $now = now();

            foreach ($targetUsers as $user) {
                $notificationsData[] = [
                    'user_id'    => $user->id,
                    'ann_id'     => $announcement->id,
                    'message'    => Str::limit($request->message, 50),
                    'link'       => 'announcements/' . $announcement->id,
                    'is_read'    => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];

                if ($user->fcm_token) {
                    NotificationHelper::sendPush(
                        $user->fcm_token,
                        "Pengumuman Baru",
                        Str::limit($request->message, 100)
                    );
                }
            }

            foreach (array_chunk($notificationsData, 200) as $chunk) {
                Notification::insert($chunk);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengumuman berhasil dibuat',
                'data' => new AnnouncementResource($announcement)
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            if (isset($fileName) && File::exists(public_path('uploads/' . $fileName))) {
                File::delete(public_path('uploads/' . $fileName));
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pengumuman',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // show
    public function show($id)
    {
        $announcement = Announcement::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new AnnouncementResource($announcement)
        ]);
    }

    // destroy
    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);

        DB::beginTransaction();
        try {
            if ($announcement->attachment_file) {
                $path = public_path('uploads/' . $announcement->attachment_file);
                if (File::exists($path)) File::delete($path);
            }

            Notification::where('ann_id', $announcement->id)->delete();
            $announcement->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengumuman berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pengumuman'
            ], 500);
        }
    }
}