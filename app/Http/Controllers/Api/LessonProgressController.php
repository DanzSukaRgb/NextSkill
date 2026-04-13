<?php

namespace App\Http\Controllers\Api;

use App\Helpers\BaseResponse;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\CourseCompletionService;
use Illuminate\Http\Request;

class LessonProgressController extends Controller
{
    /**
     * Mark lesson as completed by student
     */
    public function markComplete(Request $request, $lessonId)
    {
        try {
            $userId = auth()->id();

            // Get lesson
            $lesson = Lesson::find($lessonId);
            if (!$lesson) {
                return BaseResponse::Error('Lesson tidak ditemukan', 404);
            }

            // Check if student is enrolled in this course
            $enrollment = Enrollment::where('user_id', $userId)
                ->where('course_id', $lesson->course_id)
                ->first();

            if (!$enrollment) {
                return BaseResponse::Error('Anda tidak terdaftar di kursus ini', 403);
            }

            // Create or update lesson progress
            $lessonProgress = LessonProgress::updateOrCreate(
                [
                    'user_id' => $userId,
                    'lesson_id' => $lessonId,
                ],
                [
                    'is_completed' => true,
                    'completed_at' => now(),
                ]
            );

            // Recalculate course progress and trigger certificate creation if needed
            CourseCompletionService::updateCourseProgress($userId, $lesson->course_id);

            // Get updated enrollment for response
            $updatedEnrollment = $enrollment->fresh();

            return BaseResponse::Success('Lesson berhasil ditandai selesai', [
                'lesson_id' => $lessonId,
                'lesson_title' => $lesson->title,
                'is_completed' => $lessonProgress->is_completed,
                'completed_at' => $lessonProgress->completed_at,
                'course_progress_percentage' => $updatedEnrollment->progress_percentage,
                'has_completed_course' => $updatedEnrollment->progress_percentage >= 100,
            ], 200);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal menandai lesson selesai: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get lesson progress status
     */
    public function getProgress($lessonId)
    {
        try {
            $userId = auth()->id();

            $lesson = Lesson::find($lessonId);
            if (!$lesson) {
                return BaseResponse::Error('Lesson tidak ditemukan', 404);
            }

            // Check enrollment
            $enrollment = Enrollment::where('user_id', $userId)
                ->where('course_id', $lesson->course_id)
                ->first();

            if (!$enrollment) {
                return BaseResponse::Error('Anda tidak terdaftar di kursus ini', 403);
            }

            $lessonProgress = LessonProgress::where('user_id', $userId)
                ->where('lesson_id', $lessonId)
                ->first();

            return BaseResponse::Success('Status progress lesson', [
                'lesson_id' => $lessonId,
                'lesson_title' => $lesson->title,
                'is_completed' => $lessonProgress?->is_completed ?? false,
                'completed_at' => $lessonProgress?->completed_at,
                'course_progress_percentage' => $enrollment->progress_percentage,
            ], 200);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil progress lesson: ' . $e->getMessage(), 500);
        }
    }
}
