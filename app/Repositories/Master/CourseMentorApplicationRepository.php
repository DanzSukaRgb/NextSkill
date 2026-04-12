<?php

namespace App\Repositories\Master;

use App\Models\CourseMentorApplication;

class CourseMentorApplicationRepository
{
    // Implementasi repository untuk Course Mentor Application
    private $model;

    public function __construct(CourseMentorApplication $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 5, int $page = 1)
    {
        $model = $this->model->query();
        return $model->paginate($perPage, ['*'], 'page', $page);
    }

    public function findById(string $id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function findByCourseAndUser(string $courseId, int $userId)
    {
        return $this->model->where('course_id', $courseId)
            ->where('user_id', $userId)
            ->first();
    }

    public function updateStatus(string $id, string $status)
    {
        $application = $this->model->find($id);
        $application->update(['status' => $status]);
        return $application->fresh();
    }

    public function rejectWithReason(string $id, string $reason)
    {
        $application = $this->model->find($id);
        $application->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
        return $application->fresh();
    }

    public function listMentorApplyPending(string $mentorId)
    {
        return $this->model->where('user_id', $mentorId)
            ->where('status', 'pending')
            ->get();
    }
}