<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UsersController extends AdminBaseController
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        if ($request->has('is_active') && $request->is_active !== null) {
            $query->where('is_active', $request->is_active);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        $counts = [
            'total'    => User::count(),
            'active'   => User::where('is_active', 1)->count(),
            'inactive'   => User::where('is_active', 0)->count(),
            'teachers' => User::where('role', 'teacher')->count(),
            'admins'   => User::where('role', 'admin')->count(),
        ];

        return view('admin::users.index', compact('users', 'counts'));
    }

    public function create()
    {
        return view('admin::users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username'        => 'required|string|max:50|unique:users,username',
            'email'           => 'required|email|max:255|unique:users,email',
            'password'        => 'required|string|min:8',
            'full_name'       => 'required|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'dob'             => 'nullable|date',
            'gender'          => 'nullable|in:L,P',
            'role'            => 'required|in:admin,teacher,student,parent',
            'is_active'       => 'required|boolean',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $validated['profile_picture'] = $path;
        }

        User::create($validated);

        return redirect()->route('users.index')
                         ->with('success', 'User created successfully.');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('admin::users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin::users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username'        => 'required|string|max:50|unique:users,username,' . $user->id,
            'email'           => 'required|email|max:255|unique:users,email,' . $user->id,
            'password'        => 'nullable|string|min:8',
            'full_name'       => 'required|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'dob'             => 'nullable|date',
            'gender'          => 'nullable|in:L,P',
            'role'            => 'required|in:admin,teacher,student,parent',
            'is_active'       => 'required|boolean',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $validated['profile_picture'] = $path;
        }

        $user->update($validated);

        return redirect()->route('users.index')
                         ->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->delete();

        return redirect()->route('users.index')
                         ->with('success', 'User deleted successfully.');
    }
}