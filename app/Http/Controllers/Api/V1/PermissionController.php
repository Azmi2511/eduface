<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Permission;
use App\Models\AttendanceLog;
use App\Models\Schedule;
use App\Http\Resources\Api\V1\PermissionResource;
use App\Http\Requests\Api\V1\Permission\StorePermissionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonPeriod;
use Carbon\Carbon;

class PermissionController extends Controller
{
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

    public function store(StorePermissionRequest $request)
    {
        $user = auth()->user();
        
        if ($user->role !== 'parent') {
            return response()->json(['message' => 'Hanya orang tua yang dapat mengajukan izin'], 403);
        }

        $studentData = DB::table('students')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->where('students.id', $request->student_id)
            ->where('students.parent_id', $user->parentProfile->id)
            ->select('students.class_id', 'users.full_name')
            ->first();

        if (!$studentData) {
            return response()->json(['message' => 'Siswa tidak ditemukan dalam profil Anda'], 403);
        }

        DB::beginTransaction();

        try {
            $data = $request->validated();
            $data['parent_id'] = $user->parentProfile->id;
            $data['approval_status'] = 'Pending';

            if ($request->hasFile('proof_file')) {
                $path = $request->file('proof_file')->store('permissions', 'public');
                $data['proof_url'] = $path;
            }

            $permission = Permission::create($data);

            $teacher = DB::table('classes')
                ->join('teachers', 'classes.teacher_id', '=', 'teachers.id')
                ->where('classes.id', $studentData->class_id)
                ->select('teachers.user_id')
                ->first();

            if ($teacher && $teacher->user_id) {
                Notification::create([
                    'user_id' => $teacher->user_id,
                    'message' => "Pengajuan izin baru: " . $studentData->full_name . " (" . $permission->type . ")",
                    'link'    => "permissions/" . $permission->id, 
                    'is_read' => false
                ]);
            }

            DB::commit();

            return (new PermissionResource($permission))
                ->additional(['message' => 'Pengajuan izin berhasil dikirim']);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Gagal memproses pengajuan izin',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, Permission $permission)
    {
        $request->validate(['status' => 'required|in:Approved,Rejected']);

        if (!in_array(auth()->user()->role, ['admin', 'teacher'])) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

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
                $statusIndo = ($request->status == "Rejected") ? "Ditolak" : "Diterima";
                $studentName = $permission->student->user->full_name ?? 'Siswa';
                NotificationHelper::sendPush(
                    $userParent->fcm_token,
                    "Update Izin",
                    "Pengajuan izin " . $studentName . " telah " . strtolower($statusIndo) . "."
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
                            'time_log'     => '00:00:00',
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

    private function translateDay($englishDay)
    {
        $map = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        return $map[$englishDay] ?? $englishDay;
    }
}