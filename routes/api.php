<?php

use App\Http\Controllers\Admin\RevenueShareController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\PaymentCallbackController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\CourseMentorApplicationController;
use App\Http\Controllers\Master\CategoryController;
use App\Http\Controllers\Master\CourseController;
use App\Http\Controllers\Master\LessonController;
use App\Http\Controllers\Master\User\UserController;
use App\Http\Controllers\Mentor\QuizManagementController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('midtrans/callback', [PaymentCallbackController::class, 'callback']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('checkout', [PaymentController::class, 'checkout']);

    Route::get('leaderboard/top-learners', [LeaderboardController::class, 'topLearners']);

    Route::get('quizzes/{quizId}', [QuizController::class, 'show']);
    Route::post('quizzes/{quizId}/submit', [QuizController::class, 'submitAnswers']);
    Route::get('quizzes/{quizId}/attempts', [QuizController::class, 'getAttempts']);
    Route::get('quiz-attempts/{attemptId}/result', [QuizController::class, 'getResult']);
});

// Mentor only
Route::middleware(['auth:sanctum', 'checkRole:mentor'])->group(function () {
    Route::get('courses/no-mentor', [CourseController::class, 'listNoMentor']);
    Route::post('courses/{courseId}/apply-mentor', [CourseMentorApplicationController::class, 'apply']);
    Route::get('courses/active-by-mentor', [CourseController::class, 'listCourseActiveByMentor']);
    Route::get('course-mentor-applications/pending', [CourseMentorApplicationController::class, 'listMentorApplyPending']);

    Route::post('quizzes', [QuizManagementController::class, 'store']);
    Route::put('quizzes/{quizId}', [QuizManagementController::class, 'update']);
    Route::delete('quizzes/{quizId}', [QuizManagementController::class, 'destroy']);
    Route::get('quizzes/{quizId}/manage', [QuizManagementController::class, 'show']);
    Route::get('courses/{courseId}/quizzes', [QuizManagementController::class, 'quizzesByCourse']);

    Route::post('quizzes/{quizId}/questions/mcq', [QuizManagementController::class, 'addMCQQuestions']);
    Route::delete('questions/{questionId}', [QuizManagementController::class, 'deleteMCQQuestion']);

    Route::post('quizzes/{quizId}/questions/matching', [QuizManagementController::class, 'addMatchingPairs']);
    Route::delete('matchings/{matchingId}', [QuizManagementController::class, 'deleteMatchingPair']);
});

// Admin only
Route::middleware(['auth:sanctum', 'checkRole:admin'])->group(function () {
    // Category routes
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

    // Transaction routes
    Route::get('transactions/export/report', [TransactionController::class, 'export']);
    Route::get('transactions', [TransactionController::class, 'index']);
    Route::get('transactions/{id}', [TransactionController::class, 'show']);
    Route::put('revenue-share', [RevenueShareController::class, 'updateRevenueShare']);

    Route::get('admin/leaderboard/top-learners', [LeaderboardController::class, 'topLearners']);
});
