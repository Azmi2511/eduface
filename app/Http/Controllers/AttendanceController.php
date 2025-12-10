<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Menampilkan halaman daftar absensi.
     */
    public function index(Request $request)
    {
        // 1. Query Dasar dengan Relasi ke Siswa (User)
        // Asumsi: Model AttendanceLog memiliki fungsi 'student()' yang berelasi ke User
        $query = AttendanceLog::with('student');

        // 2. Logika Filtering (Sama seperti $_GET di native)
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%"); // Opsional: cari by NISN/Username
            });
        }

        // 3. Pagination (Menggantikan limit & offset manual)
        // Urutkan berdasarkan tanggal terbaru, lalu jam terbaru
        $attendanceLogs = $query->orderBy('date', 'desc')
                                ->orderBy('time_log', 'desc')
                                ->paginate(10);

        // 4. Statistik untuk Card (Hari ini)
        $today = Carbon::today();
        
        // Menggunakan query agregat agar lebih efisien daripada 4 query terpisah
        $stats = AttendanceLog::select('status', DB::raw('count(*) as total'))
            ->whereDate('date', $today)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $counts = [
            'present' => $stats['Hadir'] ?? 0,
            'late'    => $stats['Terlambat'] ?? 0,
            'permit'  => $stats['Izin'] ?? 0,
            'absent'  => $stats['Alpa'] ?? 0,
        ];

        // 5. Data Siswa untuk Dropdown Modal Tambah Manual
        $students = User::where('role', 'Student')->orderBy('full_name')->get();

        return view('attendance.index', compact('attendanceLogs', 'counts', 'students'));
    }

    /**
     * Menyimpan data absensi baru (Manual).
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'  => 'required|exists:users,id',
            'date'     => 'required|date',
            'time_log' => 'required',
            'status'   => 'required|in:Hadir,Terlambat,Izin,Alpa',
        ]);

        AttendanceLog::create([
            'user_id'  => $request->user_id,
            'date'     => $request->date,
            'time_log' => $request->time_log,
            'status'   => $request->status,
        ]);

        return redirect()->route('attendance.index')
            ->with('success', 'Data absensi berhasil ditambahkan.');
    }

    /**
     * Memperbarui data absensi.
     */
    public function update(Request $request, $id)
    {
        $log = AttendanceLog::findOrFail($id);

        $request->validate([
            'date'     => 'required|date',
            'time_log' => 'required',
            'status'   => 'required|in:Hadir,Terlambat,Izin,Alpa',
        ]);

        $log->update([
            'date'     => $request->date,
            'time_log' => $request->time_log,
            'status'   => $request->status,
        ]);

        return redirect()->route('attendance.index')
            ->with('success', 'Data absensi berhasil diperbarui.');
    }

    /**
     * Menghapus data absensi.
     */
    public function destroy($id)
    {
        $log = AttendanceLog::findOrFail($id);
        $log->delete();

        return redirect()->route('attendance.index')
            ->with('success', 'Data absensi berhasil dihapus.');
    }

    /**
     * Export data ke Excel (Format .xls sederhana / TSV).
     */
    public function export(Request $request)
    {
        // Gunakan filter yang sama dengan index, tapi tanpa pagination
        $query = AttendanceLog::with('student');

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderBy('date', 'desc')->orderBy('time_log', 'desc')->get();

        // Nama file
        $fileName = "data_absensi_" . date('Y-m-d') . ".xls";

        // Generate response stream download agar hemat memori
        return response()->streamDownload(function() use ($logs) {
            // Header Excel/TSV
            echo "Nama\tTanggal\tJam Masuk\tStatus\n";

            foreach ($logs as $log) {
                // Pastikan menangani jika data student terhapus (optional chaining)
                $name = $log->student->full_name ?? 'User Terhapus';
                
                echo "{$name}\t{$log->date}\t{$log->time_log}\t{$log->status}\n";
            }
        }, $fileName, [
            "Content-Type" => "application/vnd.ms-excel",
            "Pragma" => "no-cache",
            "Expires" => "0"
        ]);
    }
}