<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use App\Models\Notification;
use App\Models\Announcement;
use App\Models\User;

class AnnouncementsController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('specificUser') 
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $allUsers = User::leftJoin('students', 'users.id', '=', 'students.user_id')
            ->leftJoin('teachers', 'users.id', '=', 'teachers.user_id')
            ->where('users.is_active', 1)
            ->select(
                'users.id', 
                'users.full_name', 
                'users.role',
                'students.nisn',
                'teachers.nip'
            )
            ->orderBy('users.full_name', 'asc')
            ->get();
        return view('admin.announcements.index', compact('announcements', 'allUsers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'recipient'       => 'required|in:all,student,parent,teacher,specific',
            'user_id'         => 'required_if:recipient,specific|exists:users,id',
            'message'         => 'required|string',
            'datetime_send'   => 'required|date',
            'attachment_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
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

            $targetUserIds = [];

            if ($request->recipient === 'specific') {
                $targetUserIds[] = $request->user_id;
            } else {
                $query = User::where('is_active', 1);

                if ($request->recipient !== 'all') {
                    $query->where('role', $request->recipient);
                }
                
                $targetUserIds = $query->pluck('id')->toArray();
            }

            $notificationsData = [];
            $now = now();

            if (!empty($targetUserIds)) {
                foreach ($targetUserIds as $userId) {
                    $notificationsData[] = [
                        'user_id'    => $userId,
                        'ann_id'     => $announcement->id,
                        'message'    => 'Pengumuman Baru: ' . substr($request->message, 0, 50) . '...',
                        'link'       => 'announcements/' . $announcement->id,
                        'is_read'    => 0,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }

                foreach (array_chunk($notificationsData, 200) as $chunk) {
                    Notification::insert($chunk);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Pengumuman berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollback();

            if (isset($fileName) && File::exists(public_path('uploads/' . $fileName))) {
                File::delete(public_path('uploads/' . $fileName));
            }

            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);
        
        $request->validate([
            'message'         => 'required',
            'datetime_send'   => 'required|date',
            'attachment_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            'attachment_link' => 'nullable|url'
        ]);

        try {
            $data = [
                'message'         => $request->message,
                'sent_at'         => $request->datetime_send,
                'attachment_link' => $request->attachment_link
            ];

            if ($request->has('delete_file') && $announcement->attachment_file) {
                $path = public_path('uploads/' . $announcement->attachment_file);
                if (File::exists($path)) File::delete($path);
                $data['attachment_file'] = null;
            }

            if ($request->hasFile('attachment_file')) {
                if ($announcement->attachment_file) {
                    $oldPath = public_path('uploads/' . $announcement->attachment_file);
                    if (File::exists($oldPath)) File::delete($oldPath);
                }
                
                $file = $request->file('attachment_file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads'), $fileName);
                $data['attachment_file'] = $fileName;
            }

            $announcement->update($data);

            Notification::where('ann_id', $announcement->id)
                ->update(['message' => $request->message]);

            return redirect()->back()->with('success', 'Pengumuman diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui data.');
        }
    }

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
            return redirect()->back()->with('success', 'Pengumuman dihapus.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menghapus data.');
        }
    }

    public function show($id)
    {
        $announcement = Announcement::findOrFail($id);

        $date = Carbon::parse($announcement->created_at)->locale('id');
        
        $formattedDate = [
            'day'        => $date->format('d'),           // 16
            'month_year' => $date->isoFormat('MMM Y'),    // Des 2025
            'full'       => $date->isoFormat('D MMMM Y'), // 16 Desember 2025
            'time'       => $date->format('H:i'),         // 10:00
        ];

        $cleanFileName = $announcement->attachment_file;
        if ($cleanFileName) {
             $cleanFileName = Str::after($announcement->attachment_file, '_'); 
             if(empty($cleanFileName)) $cleanFileName = basename($announcement->attachment_file);
        }
        
        return view('announcements.show', compact('announcement', 'formattedDate', 'cleanFileName'));
    }
}