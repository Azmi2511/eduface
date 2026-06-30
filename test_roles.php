<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test each user role
$testUsers = [
    ['id' => 1, 'expected_role' => 'admin'],     // Admin
    ['id' => 3, 'expected_role' => 'teacher'],   // Teacher  
    ['id' => 5, 'expected_role' => 'student'],   // Student (has profile)
    ['id' => 2, 'expected_role' => 'student'],   // Student (NO profile - should get error)
    ['id' => 6, 'expected_role' => 'parent'],    // Parent
];

foreach ($testUsers as $testUser) {
    $user = App\Models\User::find($testUser['id']);
    if (!$user) {
        echo "User ID {$testUser['id']}: NOT FOUND\n";
        continue;
    }
    echo "\n=== User: {$user->username} (role: {$user->role}, id: {$user->id}) ===\n";

    $today = Carbon\Carbon::today()->toDateString();
    $late_limit = App\Models\SystemSetting::value('late_limit');

    try {
        if ($user->role === 'admin') {
            $total_students = App\Models\Student::count();
            echo "  total_students: $total_students\n";
            echo "  STATUS: OK\n";

        } elseif ($user->role === 'teacher') {
            $teacher = $user->teacher ?? App\Models\Teacher::where('user_id', $user->id)->first();
            echo "  teacher_id: " . ($teacher ? $teacher->id : 'NULL') . "\n";
            echo "  STATUS: OK\n";

        } elseif ($user->role === 'student') {
            $student = $user->student ?? App\Models\Student::where('user_id', $user->id)->first();
            if (!$student) {
                echo "  student profile: NULL\n";
                echo "  STATUS: ERROR - Profil siswa tidak ditemukan.\n";
            } else {
                $logs = App\Models\AttendanceLog::where('student_nisn', $student->nisn)->get();
                $total_present = $logs->where('status', 'Hadir')->count();
                $total_late = $logs->where('status', 'Terlambat')->count();
                $total_absent = $logs->whereIn('status', ['Alpha', 'Alpa'])->count();
                $total_permit = $logs->whereIn('status', ['Izin', 'Sakit'])->count();
                echo "  nisn: {$student->nisn} | logs: {$logs->count()} | hadir: $total_present | terlambat: $total_late | alpha: $total_absent | izin/sakit: $total_permit\n";
                echo "  STATUS: OK\n";
            }

        } elseif ($user->role === 'parent') {
            $parent = $user->parentProfile ?? App\Models\ParentProfile::where('user_id', $user->id)->first();
            if (!$parent) {
                echo "  parent profile: NULL\n";
                echo "  STATUS: ERROR - Profil orang tua tidak ditemukan.\n";
            } else {
                $children = App\Models\Student::where('parent_id', $parent->id)->get();
                echo "  parent_id: {$parent->id} | children: {$children->count()}\n";
                echo "  STATUS: OK\n";
            }
        }
    } catch (Exception $e) {
        echo "  STATUS: EXCEPTION - " . $e->getMessage() . "\n";
    }
}

echo "\n=== DONE ===\n";
