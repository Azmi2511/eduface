<?php

use App\Http\Controllers\Api\V1\DestinationApiController;
use App\Http\Controllers\Api\V1\HomeController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\DashboardController;
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
use App\Http\Controllers\Api\V1\RegisterController;
use App\Http\Controllers\Api\V1\FaceRecognitionController;
use App\Http\Controllers\Api\V1\UploadController;

Route::prefix('v1')->as('api.v1.')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register-data', [RegisterController::class, 'getRegisterData']);
    Route::post('/send-otp', [RegisterController::class, 'sendOtp']);
    Route::post('/verify-register', [RegisterController::class, 'verifyAndRegister']);
    Route::post('/face/predict', [FaceRecognitionController::class, 'predict']);
    Route::post('/upload-file', [UploadController::class, 'uploadFile']);
    Route::get('/student/{nisn}', [StudentController::class, 'getNameByNisn']);

    Route::get('destinations', [DestinationApiController::class, 'index']);
    Route::get('destinations/{destination}', [DestinationApiController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('home', [HomeController::class, 'index']);

        Route::post('/face/register', [FaceRecognitionController::class, 'register']);

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [UserController::class, 'me']);
        Route::put('/me/update', [ProfileController::class, 'update']);
        Route::put('/me/password', [ProfileController::class, 'updatePassword']);
        Route::post('/user/update-fcm', [UserController::class, 'updateFcmToken']);

        Route::get('available-teachers', [TeacherController::class, 'available']);

        Route::apiResource('subjects', SubjectController::class);
        Route::get('my-schedules/today', [ScheduleController::class, 'today']);
        Route::apiResource('schedules', ScheduleController::class);

        Route::get('attendance', [AttendanceController::class, 'index']);
        Route::post('attendance', [AttendanceController::class, 'store']);
        Route::post('attendance/device-input', [AttendanceController::class, 'deviceStore']);

        Route::apiResource('devices', DeviceController::class);

        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationsController::class, 'index']);
            Route::get('/unread-count', [NotificationsController::class, 'unreadCount']);
            Route::patch('/{id}/read', [NotificationsController::class, 'markAsRead']);
            Route::post('/mark-all-read', [NotificationsController::class, 'markAllRead']);
        });

        Route::middleware('role:admin,teacher,parent,student')->group(function () {
            Route::apiResource('permissions', PermissionController::class)->only(['index', 'store']);
        });

        Route::middleware('role:admin')->group(function () {
            Route::apiResource('users', UserController::class);
            Route::apiResource('teachers', TeacherController::class);
            Route::apiResource('parents', ParentController::class);
            Route::apiResource('announcements', AnnouncementController::class);

            Route::apiResource('destinations', DestinationApiController::class)
                ->except(['index', 'show']);

            Route::get('settings/backup', [SystemSettingController::class, 'backupDatabase']);
            Route::apiResource('settings', SystemSettingController::class)->only(['index', 'update']);

            Route::put('/settings/general', [SystemSettingController::class, 'updateGeneral']);
            Route::put('/settings/attendance', [SystemSettingController::class, 'updateAttendance']);
            Route::put('/settings/notification', [SystemSettingController::class, 'updateNotification']);
        });

        Route::middleware('role:admin,teacher')->group(function () {
            Route::get('class-list', [SchoolClassController::class, 'list']);
            Route::apiResource('classes', SchoolClassController::class)
                ->parameters(['classes' => 'schoolClass']);

            Route::get('stats/students', [StudentController::class, 'stats']);
            Route::apiResource('students', StudentController::class);

            Route::patch('permissions/{permission}/status', [PermissionController::class, 'updateStatus']);
        });
    });
});