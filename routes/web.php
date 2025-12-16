<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AnnouncementsController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClassesController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\TeachersController;
use App\Http\Controllers\ParentsController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FaceRecognitionController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login.show');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');

Route::get('/register', [RegisterController::class, 'show'])->name('register.show');
Route::post('/register/send-otp', [RegisterController::class, 'sendOtp'])->name('register.sendOtp');
Route::post('/register/verify-create', [RegisterController::class, 'verifyAndCreate'])->name('register.verify');

// API Endpoint untuk AJAX Request
Route::post('/auth/validate', [AuthController::class, 'validateRegistration']);
Route::post('/auth/check-otp', [AuthController::class, 'checkOtp']);
Route::post('/auth/final', [AuthController::class, 'registerFinal']);

Route::post('/attendance/store', [AttendanceController::class, 'storeAjax'])->name('attendance.storeAjax');

Route::get('/scan', function () {
    return view('scan_wajah');
})->name('scan.page');

Route::get('/registrasi-wajah', function () {
    return view('registrasi_wajah');
})->name('register.page');

Route::post('/face/register', [FaceRecognitionController::class, 'register'])->name('face.register');
Route::post('/face/predict', [FaceRecognitionController::class, 'predict'])->name('face.predict');

Route::middleware(['session.auth'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');


    Route::get('/notifications', [NotificationsController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/read', [NotificationsController::class, 'read'])->name('notifications.read');

    Route::resource('attendance', AttendanceController::class);
    Route::post('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');

    Route::get('announcements/{id}', [AnnouncementsController::class, 'show'])->name('announcements.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UsersController::class);
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::resource('teachers', TeachersController::class);
        Route::resource('parents', ParentsController::class);
        Route::resource('announcements', AnnouncementsController::class);
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.update.general');
        Route::post('/settings/attendance', [SettingsController::class, 'updateAttendance'])->name('settings.update.attendance');
        Route::post('/settings/notification', [SettingsController::class, 'updateNotification'])->name('settings.update.notification');
        Route::post('/settings/security', [SettingsController::class, 'updateSecurity'])->name('settings.update.security');
        Route::post('/settings/backup', [SettingsController::class, 'backupDatabase'])->name('settings.backup');
    });

    Route::middleware(['role:admin,teacher'])->group(function () {
        Route::resource('classes', ClassesController::class);
        Route::resource('students', StudentsController::class);
    });

    Route::middleware(['role:admin,parent'])->group(function () {
        Route::get('/parents', [ParentsController::class, 'index'])->name('parents.index');
    });

    Route::get('/students', [StudentsController::class, 'index'])
        ->middleware(['role:admin,teacher'])
        ->name('students.index');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');
});