<?php

use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\CourseMentorApplicationController;
use App\Http\Controllers\Master\CategoryController;
use App\Http\Controllers\Master\CourseController;
use App\Http\Controllers\Master\LessonController;
use App\Http\Controllers\Master\User\UserController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentCallbackController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('midtrans/callback', [PaymentCallbackController::class, 'callback']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('checkout', [PaymentController::class, 'checkout']);
    Route::put('users/{id}',[UserController::class,'update']);
});

// Admin only
Route::middleware(['auth:sanctum', 'checkRole:admin'])->group(function () {
    // Category routes
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

    // Course routes
    Route::apiResource('courses', CourseController::class);

    // Lesson routes (nested under courses)
    Route::prefix('courses/{courseId}/lessons')->group(function () {
        Route::get('', [LessonController::class, 'index']);
        Route::post('', [LessonController::class, 'store']);
        Route::get('{id}', [LessonController::class, 'show']);
        Route::put('{id}', [LessonController::class, 'update']);
        Route::delete('{id}', [LessonController::class, 'destroy']);
    });

    // Course Mentor Application routes
    Route::get('course-mentor-applications', [CourseMentorApplicationController::class, 'index']);
    Route::put('course-mentor-applications/{id}/status', [CourseMentorApplicationController::class, 'updateStatus']);

    // User routes 
    Route::get('users/mentors', [UserController::class, 'listMentors']);
    Route::apiResource('users', UserController::class)->except(['update']);
});

// Mentor only
Route::middleware(['auth:sanctum', 'checkRole:mentor'])->group(function () {
    // Mentor apply to be course mentor 
    Route::post('courses/{courseId}/apply-mentor', [CourseMentorApplicationController::class, 'apply']);
});

// Bisa di-extend untuk role lainnya:
// Route::middleware(['auth:sanctum', 'checkRole:moderator,admin'])->group(function () {
//     Route::get('reports', [...]);
//     Route::post('reports', [...]);
// });
