<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(5);

        $counts = [
            'total'    => User::count(),
            'active'   => User::where('is_active', 1)->count(),
            'teachers' => User::where('role', 'Teacher')->count(),
            'admins'   => User::where('role', 'Admin')->count(),
        ];

        return view('users.index', compact('users', 'counts'));
    }
}
