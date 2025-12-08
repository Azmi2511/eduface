<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceLog;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $logs = AttendanceLog::where('date', $date)->orderBy('created_at','desc')->get();
        return view('attendance.index', compact('logs', 'date'));
    }
}
