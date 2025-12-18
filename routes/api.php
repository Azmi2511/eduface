<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import Controllers
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\TeacherController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\ParentController;
use App\Http\Controllers\Api\V1\SchoolClassController;
use App\Http\Controllers\Api\V1\SubjectController;
use App\Http\Controllers\Api\V1\ScheduleController;
use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\NotificationsController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\SystemSettingController;

/*
|--------------------------------------------------------------------------
| API Routes - Eduface V1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // --- 🔑 PUBLIC ROUTES ---
    Route::post('/login', [AuthController::class, 'login']);
    
    // Endpoint IoT (Jika device tidak menggunakan Bearer Token, letakkan di sini)
    // Route::post('attendance/device-scan', [AttendanceController::class, 'deviceStore']);

    // --- 🛡️ PROTECTED ROUTES (Sanctum) ---
    Route::middleware('auth:sanctum')->group(function () {
        
        // 👤 User & Profile
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [UserController::class, 'me']); // Pindahkan logic closure ke UserController::me
        Route::apiResource('users', UserController::class);

        // 👨‍🏫 Teacher Management
        Route::get('available-teachers', [TeacherController::class, 'available']);
        Route::apiResource('teachers', TeacherController::class);

        // 🏫 School Class Management
        Route::get('class-list', [SchoolClassController::class, 'list']);
        Route::apiResource('classes', SchoolClassController::class)
            ->parameters(['classes' => 'schoolClass']);

        // 👨‍👩‍👧 Parent & Student Management
        Route::patch('parents/{parent}/fcm-token', [ParentController::class, 'updateFcmToken']);
        Route::apiResource('parents', ParentController::class);
        
        Route::get('stats/students', [StudentController::class, 'stats']);
        Route::apiResource('students', StudentController::class);

        // 📚 Academic: Subjects & Schedules
        Route::apiResource('subjects', SubjectController::class);
        Route::get('my-schedules/today', [ScheduleController::class, 'today']);
        Route::apiResource('schedules', ScheduleController::class);

        // 📸 Attendance & Devices (IoT)
        Route::apiResource('devices', DeviceController::class);
        Route::post('attendance/device-scan', [AttendanceController::class, 'deviceStore']);

        // 📢 Announcements (Scramble otomatis deteksi FormRequest di sini)
        // Gunakan apiResource agar Scramble bisa mapping store/update/destroy
        Route::apiResource('announcements', AnnouncementController::class);

        // 🔔 Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationsController::class, 'index']);
            Route::get('/unread-count', [NotificationsController::class, 'unreadCount']);
            Route::patch('/{id}/read', [NotificationsController::class, 'markAsRead']);
            Route::post('/mark-all-read', [NotificationsController::class, 'markAllRead']);
        });

        // 📝 Permissions (Sakit/Izin)
        Route::patch('permissions/{permission}/status', [PermissionController::class, 'updateStatus']);
        Route::apiResource('permissions', PermissionController::class);

        // ⚙️ System Settings (Admin Only)
        Route::middleware('role:admin')->group(function () {
            Route::get('settings/backup', [SystemSettingController::class, 'backupDatabase']);
            Route::apiResource('settings', SystemSettingController::class)->only(['index', 'update']);
        });

    });
});