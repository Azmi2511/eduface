<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\ParentModel;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentsController extends Controller
{
    public function index(Request $request)
    {
        // Query dengan Eager Loading
        $query = Student::with(['user', 'parent.user', 'classRoom']);

        // Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('email', 'like', "%{$search}%");
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

        // Statistik
        $count_total = Student::count();
        $count_active = User::where('role', 'student')->where('is_active', 1)->count();
        $count_inactive = User::where('role', 'student')->where('is_active', 0)->count();

        // Data untuk Dropdown Modal
        $users_student = User::where('role', 'student')->where('is_active', 1)->orderBy('full_name')->get();
        $parents = ParentModel::with('user')->get();
        $classes = ClassModel::orderBy('class_name')->get();

        return view('students.index', compact(
            'students', 
            'count_total', 'count_active', 'count_inactive',
            'users_student', 'parents', 'classes'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|unique:students,user_id',
            'nisn' => 'required|unique:students,nisn',
            'full_name' => 'required|string',
            'gender' => 'required|in:L,P',
            'class_id' => 'required|exists:classes,id',
            'parent_id' => 'required|exists:parents,id',
        ]);

        DB::transaction(function () use ($request) {
            Student::create([
                'user_id' => $request->user_id,
                'nisn' => $request->nisn,
                'full_name' => $request->full_name,
                'gender' => $request->gender,
                'class_id' => $request->class_id,
                'parent_id' => $request->parent_id,
            ]);

            User::where('id', $request->user_id)->update(['full_name' => $request->full_name]);
        });

        return redirect()->route('students.index')->with('success', 'Siswa berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $student = Student::where('user_id', $request->user_id)->firstOrFail();
        
        $request->validate([
            'nisn' => 'required|unique:students,nisn,' . $student->id,
            'full_name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $student->user_id,
            'status' => 'required|in:0,1',
        ]);

        DB::transaction(function () use ($request, $student) {
            // Update User
            $student->user->update([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'is_active' => $request->status,
            ]);

            // Update Student
            $student->update([
                'nisn' => $request->nisn,
                'full_name' => $request->full_name,
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