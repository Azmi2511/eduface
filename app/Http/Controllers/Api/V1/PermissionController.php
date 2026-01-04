<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\AttendanceLog;
use App\Http\Resources\Api\V1\PermissionResource;
use App\Http\Requests\Api\V1\Permission\StorePermissionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonPeriod;

class PermissionController extends Controller
{
    /**
     * Menampilkan daftar izin dengan filter student_id dari session Android.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Permission::with(['student.user', 'approvedBy']);

        if ($user->role === 'parent') {
            $query->where('parent_id', $user->parentProfile->id);
        }

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        return PermissionResource::collection($query->latest()->paginate(10));
    }

    /**
     * Menyimpan pengajuan izin baru dari Orang Tua.
     */
    public function store(StorePermissionRequest $request)
    {
        $user = auth()->user();
        
        if ($user->role !== 'parent') {
            return response()->json(['message' => 'Hanya orang tua yang dapat mengajukan izin'], 403);
        }

        $isChildOfMine = DB::table('student_profiles')
            ->where('id', $request->student_id)
            ->where('parent_id', $user->parentProfile->id)
            ->exists();

        if (!$isChildOfMine) {
            return response()->json(['message' => 'Siswa tidak ditemukan dalam profil Anda'], 403);
        }

        $data = $request->validated();
        $data['parent_id'] = $user->parentProfile->id;
        $data['approval_status'] = 'Pending';

        if ($request->hasFile('proof_file')) {
            $path = $request->file('proof_file')->store('permissions', 'public');
            $data['proof_url'] = $path;
        }

        $permission = Permission::create($data);

        return (new PermissionResource($permission))
            ->additional(['message' => 'Pengajuan izin berhasil dikirim']);
    }

    /**
     * Update status (Approved/Rejected) oleh Admin/Guru dan kirim Push Notification.
     */
    public function updateStatus(Request $request, Permission $permission)
    {
        $request->validate(['status' => 'required|in:Approved,Rejected']);

        // Keamanan: Hanya Guru atau Admin yang bisa mengubah status
        if (!in_array(auth()->user()->role, ['admin', 'teacher'])) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        DB::beginTransaction();
        try {
            $permission->update([
                'approval_status' => $request->status,
                'approved_by'     => auth()->id()
            ]);

            // Jika disetujui, otomatis isi tabel AttendanceLog
            if ($request->status === 'Approved') {
                $this->syncWithAttendance($permission);
            }

            // Kirim Notifikasi via FCM HTTP v1
            $userParent = $permission->parent->user;
            if ($userParent && $userParent->fcm_token) {
                NotificationHelper::sendPush(
                    $userParent->fcm_token,
                    "Update Izin: " . $request->status,
                    "Pengajuan izin " . $permission->student->user->name . " telah " . strtolower($request->status) . "."
                );
            }

            DB::commit();
            return response()->json([
                'message' => 'Status berhasil diperbarui',
                'data' => new PermissionResource($permission)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Sinkronisasi data izin ke daftar absensi harian.
     */
    private function syncWithAttendance(Permission $permission)
    {
        $period = CarbonPeriod::create($permission->start_date, $permission->end_date);

        foreach ($period as $date) {
            AttendanceLog::updateOrCreate(
                [
                    'student_id' => $permission->student_id,
                    'date'       => $date->format('Y-m-d'),
                ],
                [
                    'status'     => $permission->type, // Izin atau Sakit
                    'note'       => $permission->description,
                    'is_excused' => true
                ]
            );
        }
    }
}