<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NotificationsController extends Controller
{
    /**
     * Menampilkan daftar notifikasi pengguna.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();

        $query = Notification::where('user_id', $userId)
                             ->where('user_role', $userId); 

        if ($request->filled('search')) {
            $query->where('message', 'like', '%' . $request->search . '%');
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(10);

        $total_unread = Notification::where('user_id', $userId)
                                    ->where('user_role', $userId)
                                    ->where('is_read', 0)
                                    ->count();

        $total_data = $notifications->total();

        return view('notifications.index', compact('notifications', 'total_unread', 'total_data'));
    }
}