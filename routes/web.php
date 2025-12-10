<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnnouncementsController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClassesController;
use App\Http\Controllers\StudentsController;
use App\Http\Controllers\TeachersController;
use App\Http\Controllers\ParentsController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\SettingsController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login.show');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');

Route::middleware(['session.auth'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');

    Route::resource('announcements', AnnouncementsController::class);

    Route::get('/notifications', [NotificationsController::class, 'index'])->name('notifications.index');

    Route::resource('attendance', AttendanceController::class);
    Route::post('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');

    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UsersController::class);
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::resource('teachers', TeachersController::class);
        Route::resource('parents', ParentsController::class);
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.update.general');
        Route::post('/settings/attendance', [SettingsController::class, 'updateAttendance'])->name('settings.update.attendance');
        Route::post('/settings/notification', [SettingsController::class, 'updateNotification'])->name('settings.update.notification');
        Route::post('/settings/security', [SettingsController::class, 'updateSecurity'])->name('settings.update.security');
        Route::post('/settings/backup', [SettingsController::class, 'backupDatabase'])->name('settings.backup');
    });

    Route::middleware(['role:admin,teacher'])->group(function () {
        Route::resource('classes', ClassesController::class);
        Route::resource('students', StudentController::class);
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