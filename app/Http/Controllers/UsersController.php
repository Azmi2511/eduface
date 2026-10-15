<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UsersController extends AdminBaseController
{
    public function index(Request $request)
    {
        $query = User::with(['student.schoolClass', 'student.parent.user']);

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

        $classmodel = SchoolClass::orderBy('class_name')->get(['id', 'class_name']);
        $parents = ParentProfile::with('user')->orderBy('id')->get();

        return view('admin::users.index', compact('users', 'counts', 'classmodel', 'parents'));
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
            'student_nisn'     => 'required_if:role,student|nullable|string|max:50|unique:students,nisn',
            'student_class_id' => 'required_if:role,student|nullable|exists:classes,id',
            'student_parent_id' => 'nullable|exists:parents,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $validated['profile_picture'] = $path;
        }

        DB::transaction(function () use ($validated, $request) {
            $user = User::create($validated);

            if ($user->role === 'student') {
                Student::create([
                    'user_id' => $user->id,
                    'nisn' => $request->student_nisn,
                    'class_id' => $request->student_class_id,
                    'parent_id' => $request->student_parent_id,
                ]);
            }
        });

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
            'student_nisn'     => 'required_if:role,student|nullable|string|max:50|unique:students,nisn,' . optional($user->student)->id,
            'student_class_id' => 'required_if:role,student|nullable|exists:classes,id',
            'student_parent_id' => 'nullable|exists:parents,id',
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

        DB::transaction(function () use ($user, $validated, $request) {
            $user->update($validated);

            if ($user->role === 'student') {
                Student::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nisn' => $request->student_nisn,
                        'class_id' => $request->student_class_id,
                        'parent_id' => $request->student_parent_id,
                    ]
                );
            }
        });

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