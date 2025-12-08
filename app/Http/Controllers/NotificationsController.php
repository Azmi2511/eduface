<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationsController extends Controller
{
    public function index()
    {
        $notifications = Notification::orderBy('created_at','desc')->get();
        return view('notifications.index', compact('notifications'));
    }
}
