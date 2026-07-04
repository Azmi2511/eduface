<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Auth;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\AttendanceLog;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use App\Helpers\NotificationHelper;
use Carbon\CarbonPeriod;
use Carbon\Carbon;

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
                $q->whereHas('schoolClass.schedules', function($s) use ($user) {
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
        $user = auth()->user();

        if (!in_array($user->role, ['admin', 'teacher'])) {
            return redirect()->back()->with('error', 'Akses ditolak');
        }

        $permission = Permission::with(['student.schoolClass'])->findOrFail($id);

        if ($user->role === 'teacher') {
            $isHomeroomTeacher = DB::table('classes')
                ->where('id', $permission->student->class_id)
                ->where('teacher_id', $user->teacher->id)
                ->exists();

            if (!$isHomeroomTeacher) {
                return redirect()->back()->with('error', 'Hanya Wali Kelas atau Admin yang dapat menyetujui izin ini.');
            }
        }

        DB::beginTransaction();
        try {
            $oldStatus = $permission->approval_status;

            $permission->update([
                'approval_status' => $request->status,
                'approved_by'     => auth()->id()
            ]);

            if ($request->status === 'Approved') {
                $this->syncWithAttendance($permission);
            } elseif ($oldStatus === 'Approved' && $request->status === 'Rejected') {
                $this->cleanupAttendance($permission);
            }

            $userParent = $permission->parent?->user;
            if ($userParent && $userParent->fcm_token) {
                $statusIndo = ($request->status == "Rejected") ? "Ditolak" : "Disetujui";
                $statusText = ($statusIndo == 'Disetujui') ? "telah kami setujui" : "mohon maaf belum dapat kami setujui";
                $studentName = $permission->student->user->full_name ?? 'Siswa';
                NotificationHelper::sendPush(
                    $userParent->fcm_token,
                    "Update Izin Siswa",
                    "Pengajuan izin " . $studentName . " telah " . strtolower($statusIndo) . "."
                );
                Notification::create([
                    'user_id' => $userParent->id,
                    'title'   => "Konfirmasi Pengajuan Izin",
                    'message' => "Terkait pengajuan izin ananda " . $studentName . ", " . $statusText . ".",
                    'type'    => 'permission_update',
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Status izin berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $permission = Permission::findOrFail($id);

        if ($user->role === 'teacher') {
            $isHomeroomTeacher = DB::table('classes')
                ->where('id', $permission->student->class_id)
                ->where('teacher_id', $user->teacher->id)
                ->exists();
            if (!$isHomeroomTeacher) return redirect()->back()->with('error', 'Akses ditolak');
        }

        DB::beginTransaction();
        try {
            $this->cleanupAttendance($permission);
            $permission->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Data izin dan log absensi terkait telah dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus data.');
        }
    }

    public function show($id)
    {
        $permit = Permission::with(['student.user', 'parent.user', 'approvedBy'])->findOrFail($id);
        return view('permissions.show', compact('permit'));
    }

    private function syncWithAttendance(Permission $permission)
    {
        $student = $permission->student;
        $period = CarbonPeriod::create($permission->start_date, $permission->end_date);

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $dayName = $this->translateDay($date->format('l'));

            $schedules = Schedule::where('class_id', $student->class_id)
                ->where('day_of_week', $dayName)
                ->get();

            if ($schedules->isNotEmpty()) {
                foreach ($schedules as $sch) {
                    AttendanceLog::updateOrCreate(
                        [
                            'student_nisn' => $student->nisn,
                            'date'         => $dateString,
                            'schedule_id'  => $sch->id,
                        ],
                        [
                            'status'       => $permission->type,
                            'time_log'     => NOW(),
                            'device_id'    => null,
                        ]
                    );
                }
            }

            AttendanceLog::updateOrCreate(
                [
                    'student_nisn' => $student->nisn,
                    'date'         => $dateString,
                    'schedule_id'  => null,
                ],
                [
                    'status'       => $permission->type,
                    'time_log'     => '00:00:00',
                    'device_id'    => null,
                ]
            );
        }
    }

    private function cleanupAttendance(Permission $permission)
    {
        AttendanceLog::where('student_nisn', $permission->student->nisn)
            ->whereBetween('date', [$permission->start_date, $permission->end_date])
            ->whereNull('device_id')
            ->whereIn('status', ['Izin', 'Sakit'])
            ->delete();
    }

    private function translateDay($englishDay)
    {
        $map = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        return $map[$englishDay] ?? $englishDay;
    }
}