<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\Task;
use App\Models\TaskSubmission;
use Illuminate\Support\Str;

class CourseCompletionService
{
    /**
     * Check and complete course when progress reaches 100%
     */
    public static function checkAndCompleteCourse(Enrollment $enrollment)
    {
        // If already completed, skip
        if ($enrollment->progress_percentage >= 100) {
            return;
        }

        // Calculate progress
        $totalLessons = $enrollment->course->lessons->count();

        if ($totalLessons === 0) {
            return;
        }

        $completedLessons = LessonProgress::where('user_id', $enrollment->user_id)
            ->whereIn('lesson_id', $enrollment->course->lessons->pluck('id'))
            ->where('is_completed', true)
            ->count();

        $progressPercentage = intval(($completedLessons / $totalLessons) * 100);

        // Update enrollment progress
        $enrollment->update([
            'progress_percentage' => $progressPercentage,
            'status' => $progressPercentage >= 100 ? 'completed' : 'active',
        ]);

        // If progress reached 100%, create certificate
        if (
            $progressPercentage >= 100 && !Certificate::where('user_id', $enrollment->user_id)
                ->where('course_id', $enrollment->course_id)
                ->exists()
        ) {
            self::createCertificate($enrollment);
            self::cleanupStudentData($enrollment);
        }
    }

    /**
     * Create certificate for completed course
     */
    public static function createCertificate(Enrollment $enrollment)
    {
        $certificateNumber = 'CERT-' . strtoupper(Str::random(8)) . '-' . $enrollment->course_id;

        Certificate::create([
            'user_id' => $enrollment->user_id,
            'course_id' => $enrollment->course_id,
            'certificate_number' => $certificateNumber,
            'issued_at' => now(),
        ]);
    }

    /**
     * Clean up unnecessary data when course is completed
     * This prevents database bloat
     */
    public static function cleanupStudentData(Enrollment $enrollment)
    {
        try {
            // Delete lesson progress data (already completed)
            LessonProgress::where('user_id', $enrollment->user_id)
                ->whereIn('lesson_id', $enrollment->course->lessons->pluck('id'))
                ->delete();

            // Delete task submissions (already graded)
            $tasks = Task::whereIn('lesson_id', $enrollment->course->lessons->pluck('id'))->get();
            foreach ($tasks as $task) {
                TaskSubmission::where('user_id', $enrollment->user_id)
                    ->where('task_id', $task->id)
                    ->delete();
            }

            // Delete quiz attempts? (Optional - keep for analytics)
            // QuizAttempts::where('user_id', $enrollment->user_id)->delete();

        } catch (\Exception $e) {
            // Log error but don't fail the completion
            \Log::error('Error cleaning up student data: ' . $e->getMessage());
        }
    }

    /**
     * Update progress percentage for a course
     */
    public static function updateCourseProgress($userId, $courseId)
    {
        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->with('course.lessons')
            ->first();

        if ($enrollment) {
            self::checkAndCompleteCourse($enrollment);
        }
    }
}
