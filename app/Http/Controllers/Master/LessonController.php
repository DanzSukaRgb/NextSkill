<?php

namespace App\Http\Controllers\Master;

use App\Helpers\BaseResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\LessonRequest;
use App\Http\Resources\Master\LessonResource;
use App\Repositories\Master\LessonRepository;
use App\Services\Master\LessonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LessonController extends Controller
{
    private $repo;
    private $service;

    public function __construct(LessonRepository $repo, LessonService $service)
    {
        $this->repo = $repo;
        $this->service = $service;
    }

    /**
     * Display a listing of lessons for a course.
     */
    public function index(Request $request, string $courseId)
    {
        // Get all lessons with related quizzes
        $lessons = $this->repo->getByCourseId($courseId);

        // Get course progress if user is authenticated and enrolled
        $courseProgress = null;
        if (auth()->check()) {
            $enrollment = \App\Models\Enrollment::where('user_id', auth()->id())
                ->where('course_id', $courseId)
                ->first();
            $courseProgress = $enrollment?->progress_percentage ?? 0;
        }

        // Get unrelated quizzes (no lesson_id)
        $unrelatedQuizzes = \App\Models\Quiz::where('course_id', $courseId)
            ->whereNull('lesson_id')
            ->get()
            ->map(fn($quiz) => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'description' => $quiz->description,
                'type' => $quiz->type,
                'time_limit' => $quiz->time_limit,
                'minimum_score' => $quiz->minimum_score,
                'total_questions' => $quiz->total_questions,
            ]);

        return BaseResponse::success('Daftar lesson', [
            'data' => LessonResource::collection($lessons),
            'course_progress' => $courseProgress,
            'unrelated_quizzes' => $unrelatedQuizzes,
        ]);
    }

    /**
     * Store a newly created lesson in storage.
     */
    public function store(LessonRequest $request, string $courseId)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $lesson = $this->service->create($courseId, $data);
            DB::commit();
            return BaseResponse::Create('Lesson berhasil dibuat', new LessonResource($lesson));
        } catch (\Exception $e) {
            DB::rollback();
            return BaseResponse::error('Gagal membuat lesson: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified lesson.
     */
    public function show(string $courseId, string $id)
    {
        $lesson = $this->repo->findByCourseAndId($courseId, $id);
        if (!$lesson) {
            return BaseResponse::Error('Lesson tidak ditemukan', 404);
        }

        return BaseResponse::Success('Detail lesson', new LessonResource($lesson));
    }

    /**
     * Update the specified lesson in storage.
     */
    public function update(LessonRequest $request, string $courseId, string $id)
    {
        DB::beginTransaction();
        try {
            $check = $this->repo->findByCourseAndId($courseId, $id);
            if (!$check) {
                DB::rollback();
                return BaseResponse::Error('Lesson tidak ditemukan', 404);
            }
            $data = $request->validated();
            $lesson = $this->service->update($id, $data);
            DB::commit();
            return BaseResponse::Success('Lesson berhasil diupdate', new LessonResource($lesson));
        } catch (\Exception $e) {
            DB::rollback();
            return BaseResponse::error('Gagal update lesson: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified lesson from storage.
     */
    public function destroy(string $courseId, string $id)
    {
        DB::beginTransaction();
        try {
            $check = $this->repo->findByCourseAndId($courseId, $id);
            if (!$check) {
                DB::rollback();
                return BaseResponse::Error('Lesson tidak ditemukan', 404);
            }

            // Hapus file jika ada
            if ($check->file_path) {
                $this->service->deleteLessonFile($check->file_path);
            }

            $lesson = $this->repo->delete($id);
            DB::commit();
            return BaseResponse::Success('Lesson berhasil dihapus', new LessonResource($lesson));
        } catch (\Exception $e) {
            DB::rollback();
            return BaseResponse::error('Gagal hapus lesson: ' . $e->getMessage(), 500);
        }
    }
}
