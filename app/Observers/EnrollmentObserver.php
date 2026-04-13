<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Services\CourseCompletionService;

class EnrollmentObserver
{
    /**
     * Handle the Enrollment "updated" event.
     */
    public function updated(Enrollment $enrollment): void
    {
        // Check if progress_percentage was updated
        if ($enrollment->isDirty('progress_percentage')) {
            CourseCompletionService::checkAndCompleteCourse($enrollment);
        }
    }
}
