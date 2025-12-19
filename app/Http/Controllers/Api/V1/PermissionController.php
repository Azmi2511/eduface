<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\ParentProfile;
use App\Http\Resources\Api\V1\PermissionResource;
use App\Http\Requests\Api\V1\Permission\StorePermissionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PermissionController extends Controller
{
    /**
     * Menampilkan daftar izin.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Permission::with(['student.user', 'approvedBy']);

        // Filter berdasarkan Role
        if ($user->role === 'parent') {
            $query->where('parent_id', $user->parentProfile->id);
        } elseif ($user->role === 'teacher') {
            // Guru hanya melihat izin siswa di kelasnya (Opsional)
            // $query->whereHas('student', function($q) { ... });
        }

        return PermissionResource::collection($query->latest()->paginate(10));
    }

    /**
     * Menambahkan izin baru.
     */
    public function store(StorePermissionRequest $request)
    {
        $user = auth()->user();
        
        if ($user->role !== 'parent') {
            return response()->json(['message' => 'Hanya Orang Tua yang dapat mengajukan izin.'], 403);
        }

        $data = $request->validated();
        $data['parent_id'] = $user->parentProfile->id; // Ambil ID Parent dari profile user
        $data['approval_status'] = 'Pending';

        if ($request->hasFile('proof_file')) {
            $path = $request->file('proof_file')->store('permissions', 'public');
            $data['proof_file_path'] = $path;
        }

        $permission = Permission::create($data);

        return (new PermissionResource($permission))
            ->additional(['message' => 'Pengajuan izin berhasil dikirim.']);
    }

    /**
     * Memperbarui status izin.
     */
    public function updateStatus(Request $request, Permission $permission)
    {
        $request->validate([
            'status' => 'required|in:Approved,Rejected'
        ]);

        if (!in_array(auth()->user()->role, ['admin', 'teacher'])) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk menyetujui izin.'], 403);
        }

        $permission->update([
            'approval_status' => $request->status,
            'approved_by'     => auth()->id()
        ]);

        // Logic Tambahan: Jika Approved, buatlah AttendanceLog secara otomatis di sini
        // $this->syncWithAttendance($permission);

        return response()->json([
            'message' => "Izin telah di-{$request->status}",
            'data'    => new PermissionResource($permission)
        ]);
    }
}