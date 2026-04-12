<?php

namespace App\Services\Master;

use App\Models\CourseMentorApplication;
use App\Repositories\Master\CourseMentorApplicationRepository;
use App\Repositories\Master\CourseRepository;

class CourseMentorApplicationService
{
    private $repo;
    private $courseRepo;

    public function __construct(CourseMentorApplicationRepository $repo, CourseRepository $courseRepo)
    {
        $this->repo = $repo;
        $this->courseRepo = $courseRepo;
    }

    public function apply(string $courseId, int $userId, string $motivation): CourseMentorApplication
    {
        // Validasi course exists
        $course = $this->courseRepo->findById($courseId);
        if (!$course) {
            throw new \Exception('Kursus tidak ditemukan');
        }

        // Validasi course sudah ada mentor
        if ($course->user_id !== null) {
            throw new \Exception('Kursus ini sudah memiliki mentor');
        }

        // Validasi sudah pernah apply
        $existingApplication = $this->repo->findByCourseAndUser($courseId, $userId);
        if ($existingApplication) {
            throw new \Exception('Anda sudah apply ke kursus ini');
        }

        // Create aplikasi
        return $this->repo->create([
            'course_id' => $courseId,
            'user_id' => $userId,
            'status' => 'pending',
            'motivation' => $motivation,
        ]);
    }

    public function approve(string $applicationId): CourseMentorApplication
    {
        $application = $this->repo->findById($applicationId);
        if (!$application) {
            throw new \Exception('Aplikasi tidak ditemukan');
        }

        if ($application->status !== 'pending') {
            throw new \Exception('Aplikasi sudah di proses');
        }

        // Revalidasi course belum ada mentor (prevent race condition)
        $course = $this->courseRepo->findById($application->course_id);
        if ($course->user_id !== null) {
            throw new \Exception('Kursus ini sudah memiliki mentor');
        }

        // Assign mentor ke course
        $this->courseRepo->addMentor($application->course_id, $application->user_id);

        // Update application status
        return $this->repo->updateStatus($applicationId, 'approved');
    }

    public function reject(string $applicationId, string $reason): CourseMentorApplication
    {
        $application = $this->repo->findById($applicationId);
        if (!$application) {
            throw new \Exception('Aplikasi tidak ditemukan');
        }

        if ($application->status !== 'pending') {
            throw new \Exception('Aplikasi sudah di proses');
        }

        return $this->repo->rejectWithReason($applicationId, $reason);
    }
}
