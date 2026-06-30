<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Notification;
use App\Models\Announcement;
use App\Models\User;
use App\Helpers\NotificationHelper;

class AnnouncementsController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::with(['recipient', 'notifications.user'])->orderBy('sent_at', 'desc');

        if ($request->filled('search')) {
            $query->where('message', 'like', '%' . $request->search . '%');
        }

        $announcements = $query->paginate(10);

        $stats = [
            'total' => $announcements->total(),
            'with_attachment' => Announcement::whereNotNull('attachment_file')->count(),
        ];

        $allUsers = User::leftJoin('students', 'users.id', '=', 'students.user_id')
            ->leftJoin('teachers', 'users.id', '=', 'teachers.user_id')
            ->where('users.is_active', 1)
            ->select('users.id', 'users.full_name', 'users.role', 'students.nisn', 'teachers.nip', 'users.fcm_token')
            ->orderBy('users.full_name', 'asc')
            ->get();

        return view('admin.announcements.index', compact('announcements', 'allUsers', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'recipient' => 'required|in:all,student,parent,teacher,specific',
            'user_id' => 'required_if:recipient,specific|exists:users,id',
            'message' => 'required|string',
            'datetime_send' => 'required|date',
            'attachment_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120',
            'attachment_link' => 'nullable|url'
        ], [
            'attachment_file.mimes' => 'Format file harus PDF, DOC, Excel, atau Gambar.',
            'attachment_file.max' => 'Ukuran file maksimal adalah 5MB.',
            'attachment_link.url' => 'Format tautan link tidak valid.'
        ]);

        DB::beginTransaction();

        try {
            $data = [
                'message' => $request->message,
                'sent_at' => $request->datetime_send,
                'attachment_link' => $request->attachment_link,
                'recipient' => $request->recipient,
                'recipient_id' => $request->recipient === 'specific' ? $request->user_id : null
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
                    'user_id' => $user->id,
                    'ann_id' => $announcement->id,
                    'message' => Str::limit($request->message, 50),
                    'link' => 'announcements/' . $announcement->id,
                    'is_read' => 0,
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

            if (!empty($notificationsData)) {
                foreach (array_chunk($notificationsData, 200) as $chunk) {
                    Notification::insert($chunk);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Pengumuman berhasil dipublikasikan.');

        } catch (\Exception $e) {
            DB::rollback();
            if (isset($fileName) && File::exists(public_path('uploads/' . $fileName))) {
                File::delete(public_path('uploads/' . $fileName));
            }
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);

        DB::beginTransaction();
        try {
            if ($announcement->attachment_file) {
                $path = public_path('uploads/' . $announcement->attachment_file);
                if (File::exists($path))
                    File::delete($path);
            }

            Notification::where('ann_id', $announcement->id)->delete();
            $announcement->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Pengumuman dihapus.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menghapus data.');
        }
    }

    public function show($id)
    {
        $announcement = Announcement::findOrFail($id);
        $date = Carbon::parse($announcement->sent_at)->locale('id');

        $formattedDate = [
            'day' => $date->format('d'),
            'month_year' => $date->isoFormat('MMM Y'),
            'full' => $date->isoFormat('D MMMM Y'),
            'time' => $date->format('H:i'),
        ];

        $cleanFileName = null;
        if ($announcement->attachment_file) {
            $cleanFileName = Str::after($announcement->attachment_file, '_');
        }

        return view('admin.announcements.show', compact('announcement', 'formattedDate', 'cleanFileName'));
    }
}