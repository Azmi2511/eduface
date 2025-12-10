<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeachersController extends Controller
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

        return view('teachers.index', compact(
            'teachers',
            'count_total', 'count_active', 'count_inactive',
            'users_teacher'
        ));
    }

    /**
     * Menyimpan data guru baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'      => 'required|unique:teachers,user_id', // Pastikan user belum terdaftar sebagai guru
            'nip'          => 'required|string|max:50',
            'full_name'    => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
        ]);

        DB::transaction(function () use ($request) {
            Teacher::create([
                'user_id'      => $request->user_id,
                'nip'          => $request->nip,
                'full_name'    => $request->full_name,
                'phone_number' => $request->phone_number,
            ]);

            User::where('id', $request->user_id)->update(['full_name' => $request->full_name]);
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
            'nip'          => 'required|string|max:50',
            'full_name'    => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $id,
            'phone_number' => 'required|string|max:20',
            'status'       => 'required|in:0,1',
        ]);

        DB::transaction(function () use ($request, $teacher) {
            $teacher->user->update([
                'full_name' => $request->full_name,
                'email'     => $request->email,
                'is_active' => $request->status,
            ]);

            $teacher->update([
                'nip'          => $request->nip,
                'full_name'    => $request->full_name,
                'phone_number' => $request->phone_number,
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