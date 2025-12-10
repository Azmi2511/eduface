<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use Illuminate\Http\Request;

class ClassesController extends Controller
{
    /**
     * Menampilkan daftar kelas.
     */
    public function index(Request $request)
    {
        $query = ClassModel::query();

        // Fitur Pencarian
        if ($request->filled('search')) {
            $query->where('class_name', 'like', '%' . $request->search . '%');
        }

        // Pagination 10 data per halaman (sesuai kode native)
        $classes = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Hitung total kelas
        $total_classes = ClassModel::count();

        return view('classes.index', compact('classes', 'total_classes'));
    }

    /**
     * Menyimpan data kelas baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_name'    => 'required|string|max:255',
            'grade_level'   => 'required|string|max:50',
            'academic_year' => 'required|string|max:20',
        ]);

        ClassModel::create([
            'class_name'    => $request->class_name,
            'grade_level'   => $request->grade_level,
            'academic_year' => $request->academic_year,
        ]);

        return redirect()->route('classes.index')
            ->with('success', 'Data kelas berhasil ditambahkan.');
    }

    /**
     * Memperbarui data kelas.
     */
    public function update(Request $request, $id)
    {
        $class = ClassModel::findOrFail($id);

        $request->validate([
            'class_name'    => 'required|string|max:255',
            'grade_level'   => 'required|string|max:50',
            'academic_year' => 'required|string|max:20',
        ]);

        $class->update([
            'class_name'    => $request->class_name,
            'grade_level'   => $request->grade_level,
            'academic_year' => $request->academic_year,
        ]);

        return redirect()->route('classes.index')
            ->with('success', 'Data kelas berhasil diperbarui.');
    }

    /**
     * Menghapus data kelas.
     */
    public function destroy($id)
    {
        $class = ClassModel::findOrFail($id);
        $class->delete();

        return redirect()->route('classes.index')
            ->with('success', 'Data kelas berhasil dihapus.');
    }
}