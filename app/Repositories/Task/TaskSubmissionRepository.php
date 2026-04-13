<?php

namespace App\Repositories\Task;

use App\Models\TaskSubmission;
use Illuminate\Support\Facades\DB;

class TaskSubmissionRepository
{
    private $model;

    public function __construct(TaskSubmission $model)
    {
        $this->model = $model;
    }

    /**
     * Create or update submission
     */
    public function createOrUpdate($taskId, $userId, array $data)
    {
        return $this->model->updateOrCreate(
            [
                'task_id' => $taskId,
                'user_id' => $userId,
            ],
            $data
        );
    }

    /**
     * Find submission
     */
    public function find($submissionId)
    {
        return $this->model->find($submissionId);
    }

    /**
     * Get submission by task and user
     */
    public function getByTaskAndUser($taskId, $userId)
    {
        return $this->model->where('task_id', $taskId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Get all submissions for a task
     */
    public function getByTask($taskId)
    {
        return $this->model->where('task_id', $taskId)
            ->with(['user', 'task'])
            ->orderBy('submitted_at', 'desc')
            ->get();
    }

    /**
     * Get submitted submissions for a task
     */
    public function getSubmittedByTask($taskId)
    {
        return $this->model->where('task_id', $taskId)
            ->whereIn('status', ['submitted', 'graded'])
            ->with(['user', 'task'])
            ->orderBy('submitted_at', 'desc')
            ->get();
    }

    /**
     * Update submission with grade
     */
    public function updateWithGrade($submissionId, $score, $feedback = null)
    {
        return $this->model->find($submissionId)->update([
            'score' => $score,
            'feedback' => $feedback,
            'status' => 'graded',
        ]);
    }

    /**
     * Get submissions pending review for mentor
     */
    public function getPendingReviewByMentorCourse($mentorId)
    {
        return $this->model->whereHas('task', function ($query) use ($mentorId) {
            $query->whereHas('course', function ($q) use ($mentorId) {
                $q->where('user_id', $mentorId);
            });
        })
            ->where('status', 'submitted')
            ->with(['user', 'task.course', 'task.lesson'])
            ->orderBy('submitted_at', 'asc')
            ->get();
    }
}
