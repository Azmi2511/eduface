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
}