<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentsController extends AdminBaseController
{
    public function index(Request $request)
    {
        // Query dengan Eager Loading
        $query = Student::with(['user', 'parent.user', 'class']);
        $user = auth()->user();

        if ($user->role === 'teacher') {
            $query->whereHas('class.schedules.teacher', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Pencarian
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

        // Filter Status
        if ($request->filled('is_active')) {
            $status = $request->is_active;
            $query->whereHas('user', function($q) use ($status) {
                $q->where('is_active', $status);
            });
        }

        $students = $query->orderBy('created_at', 'desc')->paginate(10);

        $statsQuery = User::where('role', 'student');

        if ($user->role === 'teacher') {
            $statsQuery->whereHas('student.class.schedules.teacher', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        $count_total = (clone $statsQuery)->count(); 
        $count_active = (clone $statsQuery)->where('is_active', 1)->count();
        $count_inactive = (clone $statsQuery)->where('is_active', 0)->count();

        if ($user->role === 'teacher') {
            $classmodel = SchoolClass::whereHas('schedules.teacher', function($q) use ($user) {
                                $q->where('user_id', $user->id);
                            })
                            ->select('id', 'class_name')
                            ->orderBy('class_name')
                            ->distinct()
                            ->get();
        } else {
            $classmodel = SchoolClass::select('id', 'class_name')
                            ->orderBy('class_name')
                            ->get();
        }

        $users_student = User::where('role', 'student')->where('is_active', 1)->orderBy('full_name')->get();
        $parents = ParentProfile::with('user')->get();

        return view('admin::students.index', compact(
            'students', 
            'count_total', 'count_active', 'count_inactive',
            'users_student', 'parents', 'classmodel'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|unique:students,user_id',
            'nisn' => 'required|unique:students,nisn',
            'full_name' => 'required|string',
            'class_id' => 'required|exists:classes,id',
            'parent_id' => 'required|exists:parents,id',
        ]);

        DB::transaction(function () use ($request) {
            Student::create([
                'user_id' => $request->user_id,
                'nisn' => $request->nisn,
                'class_id' => $request->class_id,
                'parent_id' => $request->parent_id,
            ]);

            User::where('id', $request->user_id)->update(['full_name' => $request->full_name]);
        });

        return redirect()->route('students.index')->with('success', 'Siswa berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'nisn' => 'required|unique:students,nisn,' . $student->id,
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $student->user_id,
            'phone' => 'nullable|string|max:50',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:L,P',
            'class_id' => 'nullable|exists:classes,id',
            'parent_id' => 'nullable|exists:parents,id',
            'status' => 'required|in:0,1',
        ]);

        DB::transaction(function () use ($request, $student) {
            // Update User
            $student->user->update([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'is_active' => $request->status,
            ]);

            // Update Student
            $student->update([
                'nisn' => $request->nisn,
                'class_id' => $request->class_id,
                'parent_id' => $request->parent_id,
            ]);
        });

        return redirect()->route('students.index')->with('success', 'Data siswa diperbarui');
    }

    public function destroy($nisn)
    {
        $student = Student::where('nisn', $nisn)->firstOrFail();
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Data siswa dihapus');
    }
}