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

// Public / guest routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login.show');
Route::post('/login', [AuthController::class, 'login'])->name('login.perform');

// Protected routes (require session)
Route::middleware(['session.auth'])->group(function () {
	Route::get('/', [HomeController::class, 'index'])->name('dashboard');

	// Announcements: everyone with session can view
	Route::get('/announcements', [AnnouncementsController::class, 'index'])->name('announcements.index');
	Route::get('/announcements/{id}', [AnnouncementsController::class, 'show'])->name('announcements.show');

	// Notifications (any logged user)
	Route::get('/notifications', [NotificationsController::class, 'index'])->name('notifications.index');

	// Attendance: admin and teacher can view attendance lists
	Route::get('/attendance', [AttendanceController::class, 'index'])->middleware('role:admin,teacher')->name('attendance.index');

	// Admin-only pages
	Route::middleware('role:admin')->group(function () {
		Route::get('/users', [UsersController::class, 'index'])->name('users.index');
		Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
	});

	// Teacher/Admin pages
	Route::middleware('role:admin,teacher')->group(function () {
		Route::get('/classes', [ClassesController::class, 'index'])->name('classes.index');
		Route::get('/teachers', [TeachersController::class, 'index'])->name('teachers.index');
	});

	// Parent/Student pages
	Route::middleware('role:admin,parent')->group(function () {
		Route::get('/parents', [ParentsController::class, 'index'])->name('parents.index');
	});

	// Students listing accessible to admin/teacher
	Route::get('/students', [StudentsController::class, 'index'])->middleware('role:admin,teacher')->name('students.index');

	// Logout
	Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});


