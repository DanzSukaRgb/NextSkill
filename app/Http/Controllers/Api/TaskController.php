<?php

namespace App\Http\Controllers\Api;

use App\Helpers\BaseResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\SubmitTaskRequest;
use App\Models\Enrollment;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Repositories\Task\TaskRepository;
use App\Repositories\Task\TaskSubmissionRepository;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    private $taskRepo;
    private $taskSubmissionRepo;

    public function __construct(
        TaskRepository $taskRepo,
        TaskSubmissionRepository $taskSubmissionRepo
    ) {
        $this->taskRepo = $taskRepo;
        $this->taskSubmissionRepo = $taskSubmissionRepo;
    }

    /**
     * Get task detail for student
     */
    public function show($taskId)
    {
        try {
            $userId = auth()->id();
            $task = $this->taskRepo->findWithRelationships($taskId);

            if (!$task) {
                return BaseResponse::Error('Task tidak ditemukan', 404);
            }

            $enrollment = Enrollment::where('user_id', $userId)
                ->where('course_id', $task->course_id)
                ->first();

            if (!$enrollment) {
                return BaseResponse::Error('Anda tidak terdaftar di kursus ini', 403);
            }

            $submission = $this->taskSubmissionRepo->getByTaskAndUser($taskId, $userId);

            $taskData = [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'instruction' => $task->instruction,
                'due_date' => $task->due_date,
                'task_scope' => $task->task_scope,
                'course_id' => $task->course_id,
                'lesson_id' => $task->lesson_id,
                'submission' => $submission ? [
                    'id' => $submission->id,
                    'submission_link' => $submission->submission_link,
                    'submission_text' => $submission->submission_text,
                    'submitted_at' => $submission->submitted_at,
                    'score' => $submission->score,
                    'feedback' => $submission->feedback,
                    'status' => $submission->status,
                ] : null,
            ];

            return BaseResponse::Success('Task detail berhasil diambil', $taskData);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil task detail: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get tasks for course
     */
    public function getTasksByCourse($courseId)
    {
        try {
            $userId = auth()->id();

            $enrollment = Enrollment::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->first();

            if (!$enrollment) {
                return BaseResponse::Error('Anda tidak terdaftar di kursus ini', 403);
            }

            $tasks = $this->taskRepo->getPublishedByCourse($courseId);

            $taskData = $tasks->map(function ($task) use ($userId) {
                $submission = $this->taskSubmissionRepo->getByTaskAndUser($task->id, $userId);

                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'due_date' => $task->due_date,
                    'task_scope' => $task->task_scope,
                    'submission_status' => $submission ? $submission->status : 'not_submitted',
                    'score' => $submission ? $submission->score : null,
                ];
            });

            return BaseResponse::Success('Daftar task berhasil diambil', ['tasks' => $taskData]);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil daftar task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Submit task
     */
    public function submit(SubmitTaskRequest $request, $taskId)
    {
        try {
            $userId = auth()->id();
            $task = $this->taskRepo->find($taskId);

            if (!$task) {
                return BaseResponse::Error('Task tidak ditemukan', 404);
            }

            // Check if student is enrolled in the course
            $enrollment = Enrollment::where('user_id', $userId)
                ->where('course_id', $task->course_id)
                ->first();

            if (!$enrollment) {
                return BaseResponse::Error('Anda tidak terdaftar di kursus ini', 403);
            }

            // Check if student already submitted this task (only 1 submission allowed)
            $existingSubmission = $this->taskSubmissionRepo->getByTaskAndUser($taskId, $userId);
            if ($existingSubmission && $existingSubmission->status !== 'draft') {
                return BaseResponse::Error('Anda sudah mengumpulkan task ini. Hanya bisa dikumpulkan sekali.', 400);
            }

            $submissionData = $request->validated();
            $submissionData['submitted_at'] = now();
            $submissionData['status'] = 'submitted';

            $submission = $this->taskSubmissionRepo->createOrUpdate(
                $taskId,
                $userId,
                $submissionData
            );

            return BaseResponse::Success('Task berhasil dikumpulkan', [
                'submission_id' => $submission->id,
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at,
            ], 201);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengumpulkan task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Save task as draft
     */
    public function saveDraft(Request $request, $taskId)
    {
        try {
            $userId = auth()->id();
            $task = $this->taskRepo->find($taskId);

            if (!$task) {
                return BaseResponse::Error('Task tidak ditemukan', 404);
            }

            // Check if student is enrolled in the course
            $enrollment = Enrollment::where('user_id', $userId)
                ->where('course_id', $task->course_id)
                ->first();

            if (!$enrollment) {
                return BaseResponse::Error('Anda tidak terdaftar di kursus ini', 403);
            }

            $submissionData = [
                'submission_link' => $request->input('submission_link'),
                'submission_text' => $request->input('submission_text'),
                'status' => 'draft',
            ];

            $submission = $this->taskSubmissionRepo->createOrUpdate(
                $taskId,
                $userId,
                $submissionData
            );

            return BaseResponse::Success('Draft berhasil disimpan', [
                'submission_id' => $submission->id,
                'status' => $submission->status,
            ]);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal menyimpan draft: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all my submissions (student view)
     */
    public function mySubmissions()
    {
        try {
            $userId = auth()->id();

            $submissions = TaskSubmission::where('user_id', $userId)
                ->with(['task.course', 'task.lesson'])
                ->orderBy('submitted_at', 'desc')
                ->get();

            $submissionData = $submissions->map(function ($submission) {
                return [
                    'id' => $submission->id,
                    'task_title' => $submission->task->title,
                    'course_title' => $submission->task->course->title,
                    'submission_link' => $submission->submission_link,
                    'submission_text' => $submission->submission_text,
                    'submitted_at' => $submission->submitted_at,
                    'score' => $submission->score,
                    'feedback' => $submission->feedback,
                    'status' => $submission->status,
                ];
            });

            return BaseResponse::Success('Daftar submission berhasil diambil', ['submissions' => $submissionData]);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil daftar submission: ' . $e->getMessage(), 500);
        }
    }
}
