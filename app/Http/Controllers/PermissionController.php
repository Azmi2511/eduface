<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\DB;
use App\Helpers\NotificationHelper;
use Carbon\CarbonPeriod;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Permission::with(['student.user', 'parent.user', 'approvedBy']);

        if ($user->role === 'parent') {
            $query->where('parent_id', $user->parent->id);
        } elseif ($user->role === 'teacher') {
            $query->whereHas('student', function($q) use ($user) {
                $q->whereHas('class.schedules', function($s) use ($user) {
                    $s->where('teacher_id', $user->teacher->id);
                });
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('student.user', function($u) use ($search) {
                      $u->where('full_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('student', function($s) use ($search) {
                      $s->where('nisn', 'like', "%{$search}%");
                  });
            });
        }

        $pending_count = (clone $query)->where('approval_status', 'Pending')->count();
        $permissions = $query->latest()->paginate(10);

        return view('permissions.index', compact('permissions', 'pending_count'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:Approved,Rejected']);

        if (!in_array(auth()->user()->role, ['admin', 'teacher'])) {
            return redirect()->back()->with('error', 'Akses ditolak');
        }

        $permission = Permission::with(['student.user', 'parent.user'])->findOrFail($id);

        DB::beginTransaction();
        try {
            $permission->update([
                'approval_status' => $request->status,
                'approved_by'     => auth()->id()
            ]);

            if ($request->status === 'Approved') {
                $this->syncWithAttendance($permission);
            }

            $userParent = $permission->parent?->user;
            if ($userParent && $userParent->fcm_token) {
                $statusIndo = ($request->status == "Rejected") ? "Ditolak" : "Disetujui";
                $studentName = $permission->student->user->full_name ?? 'Siswa';
                NotificationHelper::sendPush(
                    $userParent->fcm_token,
                    "Update Izin Siswa",
                    "Pengajuan izin " . $studentName . " telah " . strtolower($statusIndo) . "."
                );
            }

            DB::commit();
            return redirect()->back()->with('success', 'Status izin berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $permit = Permission::with(['student.user', 'parent.user', 'approvedBy'])->findOrFail($id);
        return view('permissions.show', compact('permit'));
    }

    private function syncWithAttendance(Permission $permission)
    {
        $period = CarbonPeriod::create($permission->start_date, $permission->end_date);

        foreach ($period as $date) {
            AttendanceLog::updateOrCreate(
                [
                    'student_nisn' => $permission->student->nisn,
                    'date'         => $date->format('Y-m-d'),
                ],
                [
                    'status'       => $permission->type,
                    'time_log'     => now()->toTimeString(),
                    'device_id'    => null,
                    'schedule_id'  => null,
                ]
            );
        }
    }
}