<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
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
        $total_unread = Notification::where('user_id', $userId)->where('is_read', 0)->count();
        $total_data = Notification::where('user_id', $userId)->count();

        return view('notifications.index', compact('notifications', 'total_unread', 'total_data'));
    }

    public function show($id)
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);

        if (!$notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        return view('notifications.show', compact('notification'));
    }

    public function read($id)
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);

        if ($notification->is_read == 0) {
            $notification->update(['is_read' => 1]);
        }

        $targetUrl = !empty($notification->link) ? url($notification->link) : route('notifications.index');

        return redirect($targetUrl);
    }

    public function markAllRead()
    {
        $updatedCount = Notification::where('user_id', auth()->id())
                                    ->where('is_read', false)
                                    ->update(['is_read' => true]);

        if ($updatedCount > 0) {
            return redirect()->back()->with('success', "Semua $updatedCount notifikasi telah ditandai dibaca.");
        } 
        
        return redirect()->back()->with('info', 'Tidak ada notifikasi baru.');
    }

    public function destroy($id)
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notification->delete();

        return redirect()->route('notifications.index')->with('success', 'Notifikasi berhasil dihapus.');
    }

    public function destroyAll()
    {
        $deletedCount = Notification::where('user_id', auth()->id())->delete();

        if ($deletedCount > 0) {
            return redirect()->route('notifications.index')->with('success', 'Semua riwayat telah dihapus.');
        }

        return redirect()->route('notifications.index')->with('info', 'Tidak ada notifikasi.');
    }
}