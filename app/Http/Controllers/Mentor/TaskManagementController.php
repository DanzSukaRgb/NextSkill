<?php

namespace App\Http\Controllers\Mentor;

use App\Helpers\BaseResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\TaskRequest;
use App\Http\Requests\Task\GradeTaskRequest;
use App\Models\Course;
use App\Models\Task;
use App\Repositories\Task\TaskRepository;
use App\Repositories\Task\TaskSubmissionRepository;
use Illuminate\Http\Request;

class TaskManagementController extends Controller
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
     * Create new task
     */
    public function store(TaskRequest $request)
    {
        try {
            $mentorId = auth()->id();
            $course = Course::find($request->input('course_id'));

            if (!$course) {
                return BaseResponse::Error('Kursus tidak ditemukan', 404);
            }

            if ($course->user_id !== $mentorId) {
                return BaseResponse::Error('Anda tidak berhak membuat task untuk kursus ini', 403);
            }

            $data = $request->validated();
            $task = $this->taskRepo->create($data);

            return BaseResponse::Success('Task berhasil dibuat', [
                'id' => $task->id,
                'title' => $task->title,
                'course_id' => $task->course_id,
            ], 201);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal membuat task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update task
     */
    public function update(TaskRequest $request, $taskId)
    {
        try {
            $mentorId = auth()->id();
            $task = $this->taskRepo->find($taskId);

            if (!$task) {
                return BaseResponse::Error('Task tidak ditemukan', 404);
            }

            if ($task->course->user_id !== $mentorId) {
                return BaseResponse::Error('Anda tidak berhak mengubah task ini', 403);
            }

            $data = $request->validated();
            $updatedTask = $this->taskRepo->update($taskId, $data);

            return BaseResponse::Success('Task berhasil diperbarui', [
                'id' => $updatedTask->id,
                'title' => $updatedTask->title,
                'course_id' => $updatedTask->course_id,
            ]);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal memperbarui task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get task detail with submissions
     */
    public function show($taskId)
    {
        try {
            $mentorId = auth()->id();
            $task = $this->taskRepo->findWithRelationships($taskId);

            if (!$task) {
                return BaseResponse::Error('Task tidak ditemukan', 404);
            }

            if ($task->course->user_id !== $mentorId) {
                return BaseResponse::Error('Anda tidak berhak melihat task ini', 403);
            }

            $submissions = $this->taskSubmissionRepo->getByTask($taskId);

            $taskData = [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'instruction' => $task->instruction,
                'due_date' => $task->due_date,
                'task_scope' => $task->task_scope,
                'course_id' => $task->course_id,
                'lesson_id' => $task->lesson_id,
                'submissions_count' => $submissions->count(),
                'submissions_graded' => $submissions->where('status', 'graded')->count(),
                'submissions' => $submissions->map(function ($submission) {
                    return [
                        'id' => $submission->id,
                        'student_name' => $submission->user->name,
                        'student_id' => $submission->user->id,
                        'submission_link' => $submission->submission_link,
                        'submission_text' => $submission->submission_text,
                        'submitted_at' => $submission->submitted_at,
                        'score' => $submission->score,
                        'feedback' => $submission->feedback,
                        'status' => $submission->status,
                    ];
                }),
            ];

            return BaseResponse::Success('Task detail berhasil diambil', $taskData);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil task detail: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete task
     */
    public function destroy($taskId)
    {
        try {
            $mentorId = auth()->id();
            $task = $this->taskRepo->find($taskId);

            if (!$task) {
                return BaseResponse::Error('Task tidak ditemukan', 404);
            }

            if ($task->course->user_id !== $mentorId) {
                return BaseResponse::Error('Anda tidak berhak menghapus task ini', 403);
            }

            $this->taskRepo->delete($taskId);

            return BaseResponse::Success('Task berhasil dihapus', null);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal menghapus task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get tasks by course
     */
    public function tasksByCourse($courseId)
    {
        try {
            $mentorId = auth()->id();
            $course = Course::find($courseId);

            if (!$course) {
                return BaseResponse::Error('Kursus tidak ditemukan', 404);
            }

            if ($course->user_id !== $mentorId) {
                return BaseResponse::Error('Anda tidak berhak melihat task kursus ini', 403);
            }

            $tasks = $this->taskRepo->getByCourse($courseId);

            $taskData = $tasks->map(function ($task) {
                $pendingSubmissions = $task->submissions->where('status', 'submitted')->count();
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'due_date' => $task->due_date,
                    'task_scope' => $task->task_scope,
                    'total_submissions' => $task->submissions->count(),
                    'pending_submissions' => $pendingSubmissions,
                    'graded_submissions' => $task->submissions->where('status', 'graded')->count(),
                ];
            });

            return BaseResponse::Success('Daftar task berhasil diambil', ['tasks' => $taskData]);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil daftar task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Grade task submission
     */
    public function gradeSubmission(GradeTaskRequest $request, $submissionId)
    {
        try {
            $mentorId = auth()->id();
            $submission = $this->taskSubmissionRepo->find($submissionId);

            if (!$submission) {
                return BaseResponse::Error('Submission tidak ditemukan', 404);
            }

            if ($submission->task->course->user_id !== $mentorId) {
                return BaseResponse::Error('Anda tidak berhak menilai submission ini', 403);
            }

            $updatedSubmission = $this->taskSubmissionRepo->updateWithGrade(
                $submissionId,
                $request->input('score'),
                $request->input('feedback')
            );

            return BaseResponse::Success('Nilai berhasil diberikan', $updatedSubmission);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal memberikan nilai: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get mentor courses with pending tasks count
     */
    public function getMentorCoursesPendingTasks()
    {
        try {
            $mentorId = auth()->id();

            // Get all courses owned by this mentor
            $courses = Course::where('user_id', $mentorId)
                ->with(['tasks.submissions.user'])
                ->get();

            $coursesData = $courses->map(function ($course) {
                $totalTasks = $course->tasks->count();
                $pendingTasks = 0;
                $totalStudents = collect();
                $pendingStudents = collect();

                foreach ($course->tasks as $task) {
                    foreach ($task->submissions as $submission) {
                        $totalStudents->push($submission->user_id);
                        if ($submission->status === 'submitted') {
                            $pendingTasks++;
                            $pendingStudents->push($submission->user_id);
                        }
                    }
                }

                return [
                    'id' => $course->id,
                    'course_name' => $course->title,
                    'total_tasks' => $totalTasks,
                    'total_students_submitted' => $totalStudents->unique()->count(),
                    'students_pending_grading' => $pendingStudents->unique()->count(),
                    'pending_grading' => $pendingTasks,
                ];
            });

            return BaseResponse::Success('Daftar course dengan tugas yang perlu dinilai', $coursesData);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil daftar course: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get student submissions by course with filters and search
     */
    public function getStudentSubmissionsByCourse(Request $request, $courseId)
    {
        try {
            $mentorId = auth()->id();
            $course = Course::find($courseId);

            if (!$course) {
                return BaseResponse::Error('Kursus tidak ditemukan', 404);
            }

            if ($course->user_id !== $mentorId) {
                return BaseResponse::Error('Anda tidak berhak melihat course ini', 403);
            }

            $status = $request->query('status'); // 'pending', 'graded', atau null (semua)
            $search = $request->query('search'); // search nama siswa

            // Get all tasks for this course with submissions
            $tasks = $course->tasks()->with('submissions.user')->get();

            $submissions = collect();
            foreach ($tasks as $task) {
                foreach ($task->submissions as $submission) {
                    $submissions->push([
                        'submission_id' => $submission->id,
                        'student_name' => $submission->user->name,
                        'student_id' => $submission->user->id,
                        'task_title' => $task->title,
                        'course_name' => $course->title,
                        'submitted_at' => $submission->submitted_at,
                        'status' => $submission->status,
                        'score' => $submission->score,
                        'feedback' => $submission->feedback,
                    ]);
                }
            }

            // Filter by status
            if ($status) {
                $submissions = $submissions->where('status', $status);
            }

            // Search by student name
            if ($search) {
                $submissions = $submissions->filter(function ($item) use ($search) {
                    return stripos($item['student_name'], $search) !== false;
                });
            }

            // Sort by submitted_at descending
            $submissions = $submissions->sortByDesc('submitted_at')->values();

            return BaseResponse::Success('Daftar submission siswa berhasil diambil', $submissions);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil daftar submission: ' . $e->getMessage(), 500);
        }
    }


}
