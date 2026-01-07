<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request){
        $user = auth()->user();
        $query = Permission::with(['student.user', 'approvedBy']);

        if ($user->role === 'parent') {
            $query->where('parent_id', $user->parentProfile->id);
        } elseif ($user->role === 'teacher') {
            $query->whereHas('class.schedules.teacher', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $permissions = $query->get();

        return view('permissions.index', compact('permissions'));
    }
}