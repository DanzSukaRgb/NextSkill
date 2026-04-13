<?php

namespace App\Repositories\Task;

use App\Models\Task;
use Illuminate\Support\Facades\DB;

class TaskRepository
{
    private $model;

    public function __construct(Task $model)
    {
        $this->model = $model;
    }

    /**
     * Create new task
     */
    public function create(array $data)
    {
        return Task::create($data);
    }

    /**
     * Update task
     */
    public function update($taskId, array $data)
    {
        $task = Task::find($taskId);
        if ($task) {
            $task->update($data);
        }
        return $task;
    }

    /**
     * Find task by id
     */
    public function find($taskId)
    {
        return $this->model->find($taskId);
    }

    /**
     * Get task by id with relationships
     */
    public function findWithRelationships($taskId)
    {
        return $this->model->with(['course', 'lesson', 'submissions.user'])->find($taskId);
    }

    /**
     * Get tasks by course
     */
    public function getByCourse($courseId)
    {
        return $this->model->where('course_id', $courseId)
            ->with(['lesson'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Delete task with all related submissions
     */
    public function delete($taskId)
    {
        return DB::transaction(function () use ($taskId) {
            $task = $this->model->find($taskId);
            if ($task) {
                $task->submissions()->delete();
                return $task->delete();
            }
            return false;
        });
    }

    /**
     * Get tasks by course
     */
    public function getPublishedByCourse($courseId)
    {
        return $this->model->where('course_id', $courseId)
            ->with(['lesson'])
            ->get();
    }
}
