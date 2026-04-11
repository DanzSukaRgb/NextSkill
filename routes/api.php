<?php

use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\Master\CategoryController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
});

// Category management - Admin only
Route::middleware(['auth:sanctum', 'checkRole:admin'])->group(function () {
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
});

// Bisa di-extend untuk role lainnya:
// Route::middleware(['auth:sanctum', 'checkRole:moderator,admin'])->group(function () {
//     Route::get('reports', [...]);
//     Route::post('reports', [...]);
// });
