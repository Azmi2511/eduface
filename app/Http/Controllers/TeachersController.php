<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeachersController extends AdminBaseController
{
    /**
     * Menampilkan daftar guru.
     */
    public function index(Request $request)
    {
        $query = Teacher::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $status = $request->is_active;
            $query->whereHas('user', function($q) use ($status) {
                $q->where('is_active', $status);
            });
        }

        $teachers = $query->orderBy('created_at', 'desc')->paginate(10);

        $count_total = Teacher::count();
        $count_active = User::where('role', 'teacher')->where('is_active', 1)->count();
        $count_inactive = User::where('role', 'teacher')->where('is_active', 0)->count();

        $users_teacher = User::where('role', 'teacher')->where('is_active', 1)->orderBy('full_name')->get();

        return view('admin::teachers.index', compact(
            'teachers',
            'count_total', 'count_active', 'count_inactive',
            'users_teacher'
        ));
    }

    /**
     * Generate teacher code dengan pola TCH-YYYY-NNNN
     * Contoh: TCH-2025-0001, TCH-2025-0002, dst
     */
    private function generateTeacherCode()
    {
        $year = date('Y');
        $prefix = "TCH-{$year}-";
        
        // Cari nomor urut terakhir untuk tahun ini
        $lastTeacher = Teacher::where('teacher_code', 'like', $prefix . '%')
            ->orderBy('teacher_code', 'desc')
            ->first();
        
        if ($lastTeacher && $lastTeacher->teacher_code) {
            // Extract nomor urut dari kode terakhir (format: TCH-YYYY-NNNN)
            $lastNumber = (int) substr($lastTeacher->teacher_code, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            // Jika belum ada, mulai dari 1
            $nextNumber = 1;
        }
        
        // Format nomor dengan padding 4 digit
        $teacherCode = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        
        // Pastikan kode unik (jika ada duplikasi, increment)
        while (Teacher::where('teacher_code', $teacherCode)->exists()) {
            $nextNumber++;
            $teacherCode = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }
        
        return $teacherCode;
    }

    /**
     * Menyimpan data guru baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'          => 'required|unique:teachers,user_id', // Pastikan user belum terdaftar sebagai guru
            'nip'              => 'required|string|max:50|unique:teachers,nip',
            'employment_status' => 'nullable|in:PNS,Honorer,Kontrak',
        ]);

        DB::transaction(function () use ($request) {
            // Generate teacher_code otomatis
            $teacherCode = $this->generateTeacherCode();
            
            Teacher::create([
                'user_id'          => $request->user_id,
                'nip'              => $request->nip,
                'employment_status' => $request->employment_status,
                'teacher_code'     => $teacherCode,
            ]);
        });

        return redirect()->route('teachers.index')
            ->with('success', 'Data guru berhasil ditambahkan.');
    }

    /**
     * Memperbarui data guru.
     */
    public function update(Request $request, $id)
    {
        $teacher = Teacher::where('user_id', $id)->firstOrFail();

        $request->validate([
            'nip'              => 'required|string|max:50',
            'full_name'        => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email,' . $id,
            'phone'            => 'nullable|string|max:50',
            'dob'              => 'nullable|date',
            'gender'           => 'nullable|in:L,P',
            'employment_status' => 'nullable|in:PNS,Honorer,Kontrak',
            'teacher_code'     => 'nullable|string|max:50',
            'status'           => 'required|in:0,1',
        ]);

        DB::transaction(function () use ($request, $teacher) {
            $teacher->user->update([
                'full_name' => $request->full_name,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'dob'       => $request->dob,
                'gender'    => $request->gender,
                'is_active' => $request->status,
            ]);

            $teacher->update([
                'nip'              => $request->nip,
                'employment_status' => $request->employment_status,
                'teacher_code'     => $request->teacher_code,
            ]);
        });

        return redirect()->route('teachers.index')
            ->with('success', 'Data guru berhasil diperbarui.');
    }

    /**
     * Menghapus data guru.
     */
    public function destroy($id)
    {
        $teacher = Teacher::where('user_id', $id)->firstOrFail();
        $teacher->delete();

        return redirect()->route('teachers.index')
            ->with('success', 'Data guru berhasil dihapus.');
    }
}