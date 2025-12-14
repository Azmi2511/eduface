<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Notification;
use App\Models\Announcement;

class AnnouncementsController extends AdminBaseController
{
    public function index()
    {
        $announcements = Announcement::orderBy('datetime_send', 'desc')->paginate(10);
        return view('admin::announcements.index', compact('announcements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'recipient' => 'required',
            'message' => 'required',
            'datetime_send' => 'required|date',
            'attachment_file' => 'nullable|file|max:2048',
            'attachment_link' => 'nullable|url'
        ]);

        $data = $request->all();

        if ($request->hasFile('attachment_file')) {
            $fileName = time() . '_' . $request->file('attachment_file')->getClientOriginalName();
            $request->file('attachment_file')->move(public_path('uploads'), $fileName);
            $data['attachment_file'] = $fileName;
        }

        Announcement::create($data);

        return redirect()->back()->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);
        
        if ($request->has('delete_file') && $announcement->attachment_file) {
            $path = public_path('uploads/' . $announcement->attachment_file);
            if (file_exists($path)) unlink($path);
            $announcement->attachment_file = null;
        }

        if ($request->hasFile('attachment_file')) {
            if ($announcement->attachment_file) {
                $oldPath = public_path('uploads/' . $announcement->attachment_file);
                if (file_exists($oldPath)) unlink($oldPath);
            }
            
            $fileName = time() . '_' . $request->file('attachment_file')->getClientOriginalName();
            $request->file('attachment_file')->move(public_path('uploads'), $fileName);
            $announcement->attachment_file = $fileName;
        }

        $announcement->update($request->except(['attachment_file', 'delete_file']));

        return redirect()->back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        
        if ($announcement->attachment_file) {
            $path = public_path('uploads/' . $announcement->attachment_file);
            if (file_exists($path)) unlink($path);
        }
        
        $announcement->delete();
        return redirect()->back()->with('success', 'Pengumuman dihapus.');
    }

    public function show($id)
    {
        $announcement = Announcement::find($id);

        if (!$announcement) {
            return view('errors.404_custom', [
                'message' => 'Pengumuman tidak ditemukan atau telah dihapus.',
                'back_url' => route('dashboard')
            ]);
        }

        $user = Auth::user();
        Notification::where('user_id', $user->id)
            ->where('ann_id', $id)
            ->update(['is_read' => 1]);

        $date = Carbon::parse($announcement->datetime_send);
        $formattedDate = [
            'full' => $date->translatedFormat('d F Y'),
            'time' => $date->format('H:i'),
            'day' => $date->format('d'),
            'month_year' => $date->format('M Y'),
        ];

        $cleanFileName = null;
        if ($announcement->attachment_file) {
            $parts = explode('_', $announcement->attachment_file, 2);
            $cleanFileName = $parts[1] ?? $announcement->attachment_file;
        }

        return view('announcements.show', compact('announcement', 'formattedDate', 'cleanFileName'));
    }
}
