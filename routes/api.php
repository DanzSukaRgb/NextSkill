<?php

use App\Http\Controllers\Admin\RevenueShareController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\AdminWithdrawalController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\PaymentCallbackController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\StudentCourseController;
use App\Http\Controllers\Api\LessonProgressController;
use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\CourseMentorApplicationController;
use App\Http\Controllers\Master\CategoryController;
use App\Http\Controllers\Master\CourseController;
use App\Http\Controllers\Master\LessonController;
use App\Http\Controllers\Master\User\UserController;
use App\Http\Controllers\Mentor\QuizManagementController;
use App\Http\Controllers\Mentor\TaskManagementController;
use App\Http\Controllers\Mentor\StudentManagementController;
use App\Http\Controllers\Mentor\MentorWithdrawalController;
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

    Route::get('courses', [CourseController::class, 'index']);
    Route::get('courses/no-mentor', [CourseController::class, 'listNoMentor'])->middleware('checkRole:mentor');
    Route::get('courses/active-by-mentor', [CourseController::class, 'listCourseActiveByMentor'])->middleware('checkRole:mentor');
    Route::get('courses/{id}', [CourseController::class, 'show']);

    Route::get('leaderboard/top-learners', [LeaderboardController::class, 'topLearners']);

    Route::get('quizzes/{quizId}', [QuizController::class, 'show']);
    Route::post('quizzes/{quizId}/submit', [QuizController::class, 'submitAnswers']);
    Route::get('quizzes/{quizId}/attempts', [QuizController::class, 'getAttempts']);
    Route::get('quiz-attempts/{attemptId}/result', [QuizController::class, 'getResult']);

    Route::get('tasks/{taskId}', [TaskController::class, 'show']);
    Route::get('courses/{courseId}/tasks', [TaskController::class, 'getTasksByCourse']);
    Route::post('tasks/{taskId}/submit', [TaskController::class, 'submit']);
    Route::post('tasks/{taskId}/save-draft', [TaskController::class, 'saveDraft']);
    Route::get('submissions/my-submissions', [TaskController::class, 'mySubmissions']);

    Route::get('my-courses', [StudentCourseController::class, 'getMyCourses']);
    Route::get('courses/{courseId}/detail', [StudentCourseController::class, 'getCourseDetail']);
    Route::get('my-certificates', [StudentCourseController::class, 'getMyCertificates']);
    Route::get('certificates/{certificateId}/download', [StudentCourseController::class, 'downloadCertificatePDF']);

    Route::post('lessons/{lessonId}/mark-complete', [LessonProgressController::class, 'markComplete']);
    Route::get('lessons/{lessonId}/progress', [LessonProgressController::class, 'getProgress']);
});

// Mentor only
Route::middleware(['auth:sanctum', 'checkRole:mentor'])->group(function () {
    Route::post('courses/{courseId}/apply-mentor', [CourseMentorApplicationController::class, 'apply']);
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

    Route::post('tasks', [TaskManagementController::class, 'store']);
    Route::put('tasks/{taskId}', [TaskManagementController::class, 'update']);
    Route::delete('tasks/{taskId}', [TaskManagementController::class, 'destroy']);
    Route::get('tasks/{taskId}/manage', [TaskManagementController::class, 'show']);
    Route::get('courses/{courseId}/tasks/manage', [TaskManagementController::class, 'tasksByCourse']);
    Route::get('courses/{courseId}/submissions', [TaskManagementController::class, 'getStudentSubmissionsByCourse']);
    Route::get('mentor/courses-pending-tasks', [TaskManagementController::class, 'getMentorCoursesPendingTasks']);
    Route::put('task-submissions/{submissionId}/grade', [TaskManagementController::class, 'gradeSubmission']);

    Route::get('students', [StudentManagementController::class, 'index']);
    Route::get('students/filters', [StudentManagementController::class, 'getFilters']);
    Route::get('students/{studentId}', [StudentManagementController::class, 'show']);

    // Withdrawal routes
    Route::get('balance', [MentorWithdrawalController::class, 'getBalance']);
    Route::get('income-statistics', [MentorWithdrawalController::class, 'getIncomeStatistics']);
    Route::post('withdrawal-request', [MentorWithdrawalController::class, 'requestWithdrawal']);
    Route::get('withdrawal-history', [MentorWithdrawalController::class, 'getWithdrawalHistory']);

    Route::prefix('courses/{courseId}/lessons')->group(function () {
        Route::get('', [LessonController::class, 'index']);
        Route::post('', [LessonController::class, 'store']);
        Route::get('{id}', [LessonController::class, 'show']);
        Route::put('{id}', [LessonController::class, 'update']);
        Route::delete('{id}', [LessonController::class, 'destroy']);
    });
});

// Admin only
Route::middleware(['auth:sanctum', 'checkRole:admin'])->group(function () {
    // Category routes
    Route::post('categories', [CategoryController::class, 'store']);
    Route::get('categories/{id}', [CategoryController::class, 'show']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

    // Course routes
    Route::apiResource('courses', CourseController::class)->except(['index', 'show']);
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
    Route::get('revenue-share', [RevenueShareController::class, 'getCurrentRevenueShare']);

    Route::get('admin/leaderboard/top-learners', [LeaderboardController::class, 'topLearners']);

    // Withdrawal management routes
    Route::get('withdrawals', [AdminWithdrawalController::class, 'index']);
    Route::get('withdrawals/statistics', [AdminWithdrawalController::class, 'getStatistics']);
    Route::post('withdrawals/{id}/approve', [AdminWithdrawalController::class, 'approve']);
    Route::post('withdrawals/{id}/reject', [AdminWithdrawalController::class, 'reject']);
});
