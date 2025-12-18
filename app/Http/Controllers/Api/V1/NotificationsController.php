<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Http\Resources\Api\V1\NotificationResource;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    /**
     * Ambil semua notifikasi user yang login
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Notification::where('user_id', $user->id);

        // Filter: Hanya yang belum dibaca
        if ($request->get('unread_only') === 'true') {
            $query->where('is_read', 0);
        }

        $notifications = $query->latest('created_at')->paginate(15);

        return NotificationResource::collection($notifications)
            ->additional([
                'meta' => [
                    'unread_count' => Notification::where('user_id', $user->id)->where('is_read', 0)->count()
                ]
            ]);
    }

    /**
     * Endpoint Cepat untuk Badge Notifikasi
     */
    public function unreadCount()
    {
        $count = Notification::where('user_id', auth()->id())
            ->where('is_read', 0)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Tandai satu notifikasi sebagai dibaca
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);
        
        $notification->update(['is_read' => 1]);

        return response()->json(['message' => 'Notifikasi ditandai sebagai dibaca']);
    }

    /**
     * Tandai SEMUA sebagai dibaca
     */
    public function markAllRead()
    {
        $updated = Notification::where('user_id', auth()->id())
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        return response()->json([
            'message' => "{$updated} notifikasi berhasil diperbarui",
            'updated_count' => $updated
        ]);
    }
}