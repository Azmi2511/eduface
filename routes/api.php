<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/auth/login', [AuthController::class, 'apiLogin']);

Route::middleware('jwt')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'apiLogout']);
    Route::get('/user', function (Request $request) {
        $payload = $request->attributes->get('jwt_payload');
        return response()->json($payload);
    });

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users', function () {
            return response()->json(['message' => 'Admin users endpoint']);
        });
    });

    // Teacher routes
    Route::middleware('role:teacher,admin')->group(function () {
        Route::get('/teacher/classes', function () {
            return response()->json(['message' => 'Teacher classes endpoint']);
        });
    });

    // Parent routes
    Route::middleware('role:parent,admin')->group(function () {
        Route::get('/parent/children', function () {
            return response()->json(['message' => 'Parent children endpoint']);
        });
    });
});

