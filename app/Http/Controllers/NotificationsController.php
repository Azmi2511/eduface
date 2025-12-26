<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        $query = Notification::where('user_id', $userId);

        if ($request->filled('search')) {
            $query->where('message', 'like', '%' . $request->search . '%');
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(10);

        $total_unread = Notification::where('user_id', $userId)
            ->where('is_read', 0)
            ->count();

        $total_data = Notification::where('user_id', $userId)->count();

        return view('notifications.index', compact('notifications', 'total_unread', 'total_data'));
    }

    public function read($id)
    {
        $notification = Notification::where('user_id', auth()->id())
                                    ->where('id', $id)
                                    ->firstOrFail();

        if ($notification->is_read == 0) {
            $notification->is_read = 1;
            $notification->save();
        }

        $targetUrl = !empty($notification->link) ? url($notification->link) : route('notifications.index');

        return redirect($targetUrl);
    }
    public function markAllRead()
    {
        $updatedCount = Notification::where('user_id', auth()->id())
                                    ->where('is_read', false)
                                    ->update(['is_read' => true]);

        $message = ($updatedCount > 0) 
            ? "Semua $updatedCount notifikasi baru telah ditandai sebagai sudah dibaca." 
            : "Tidak ada notifikasi baru untuk ditandai.";

        if ($updatedCount > 0) {
            return redirect()->back()->with('success', 'Notifikasi berhasil diperbarui.');
        } else {
            return redirect()->back()->with('info', 'Tidak ada notifikasi baru untuk ditandai.');
        }
    }
}