<?php

namespace App\Http\Controllers\Mentor;

use App\Helpers\BaseResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quiz\MatchingQuestionsRequest;
use App\Http\Requests\Quiz\MCQQuestionsRequest;
use App\Http\Requests\Quiz\QuizRequest;
use App\Models\Course;
use App\Models\Quiz;
use App\Repositories\Master\CourseRepository;
use App\Repositories\Quiz\QuizMatchingRepository;
use App\Repositories\Quiz\QuizQuestionRepository;
use App\Repositories\Quiz\QuizRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizManagementController extends Controller
{
    private $quizRepo;
    private $courseRepo;
    private $quizMatchingRepo;
    private $quizQuestionRepo;

    public function __construct(
        QuizRepository $quizRepo,
        CourseRepository $courseRepo,
        QuizMatchingRepository $quizMatchingRepo,
        QuizQuestionRepository $quizQuestionRepo
    ) {
        $this->quizRepo = $quizRepo;
        $this->courseRepo = $courseRepo;
        $this->quizMatchingRepo = $quizMatchingRepo;
        $this->quizQuestionRepo = $quizQuestionRepo;
    }

    /**
     * Create new quiz
     */
    public function store(QuizRequest $request)
    {
        try {
            $mentorId = auth()->id();

            $course = $this->courseRepo->find($request->input('course_id'));
            if (!$course || $course->user_id !== $mentorId) {
                return BaseResponse::Error('Unauthorized course access', 403);
            }

            DB::beginTransaction();
            $quiz = $this->quizRepo->create($request->validated());
            DB::commit();

            return BaseResponse::Success('Quiz created successfully', $quiz, 201);
        } catch (\Exception $e) {
            DB::rollback();
            return BaseResponse::Error('Failed to create quiz: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get quiz details
     */
    public function show($quizId)
    {
        $mentorId = auth()->id();

        $quiz = $this->quizRepo->quizWithCourseAndLesson($quizId);

        if (!$quiz) {
            return BaseResponse::Error('Quiz not found', 404);
        }

        if ($quiz->course->user_id !== $mentorId) {
            return BaseResponse::Error('Unauthorized quiz access', 403);
        }

        return BaseResponse::Success('Quiz retrieved', $quiz);
    }

    /**
     * Update quiz
     */
    public function update(QuizRequest $request, $quizId)
    {
        try {
            $mentorId = auth()->id();
            $quiz = $this->quizRepo->quizWithCourseAndLesson($quizId);

            if (!$quiz) {
                return BaseResponse::Error('Quiz not found', 404);
            }

            if ($quiz->course->user_id !== $mentorId) {
                return BaseResponse::Error('Unauthorized quiz access', 403);
            }

            $updated = $this->quizRepo->update($quizId, $request->validated());

            return BaseResponse::Success('Quiz updated successfully', $updated);
        } catch (\Exception $e) {
            return BaseResponse::Error('Failed to update quiz: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete quiz
     */
    public function destroy($quizId)
    {
        try {
            $mentorId = auth()->id();
            $quiz = $this->quizRepo->quizWithCourseAndLesson($quizId);

            if (!$quiz) {
                return BaseResponse::Error('Quiz not found', 404);
            }

            if ($quiz->course->user_id !== $mentorId) {
                return BaseResponse::Error('Unauthorized quiz access', 403);
            }

            $this->quizRepo->delete($quizId);

            return BaseResponse::Success('Quiz deleted successfully', null);
        } catch (\Exception $e) {
            return BaseResponse::Error('Failed to delete quiz: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Add MCQ questions to quiz
     */
    public function addMCQQuestions(MCQQuestionsRequest $request, $quizId)
    {
        try {
            $mentorId = auth()->id();
            $quiz = $this->quizRepo->quizWithCourseAndLesson($quizId);

            if (!$quiz) {
                return BaseResponse::Error('Quiz not found', 404);
            }

            if ($quiz->course->user_id !== $mentorId) {
                return BaseResponse::Error('Unauthorized quiz access', 403);
            }

            if ($quiz->type !== 'MCQ') {
                return BaseResponse::Error('This quiz is not MCQ type', 400);
            }

            $questions = $this->quizRepo->addMCQQuestions($quizId, $request->input('questions'));

            return BaseResponse::Success('MCQ questions added successfully', [
                'questions_added' => count($questions),
                'quiz_total_questions' => $quiz->fresh()->total_questions,
            ], 201);
        } catch (\Exception $e) {
            return BaseResponse::Error('Failed to add questions: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Add matching pairs to quiz
     */
    public function addMatchingPairs(MatchingQuestionsRequest $request, $quizId)
    {
        try {
            $mentorId = auth()->id();
            $quiz = $this->quizRepo->quizWithCourseAndLesson($quizId);

            if (!$quiz) {
                return BaseResponse::Error('Quiz not found', 404);
            }

            if ($quiz->course->user_id !== $mentorId) {
                return BaseResponse::Error('Unauthorized quiz access', 403);
            }

            if ($quiz->type !== 'Matching') {
                return BaseResponse::Error('This quiz is not Matching type', 400);
            }

            $matchings = $this->quizRepo->addMatchingQuestions($quizId, $request->input('pairs'));

            return BaseResponse::Success('Matching pairs added successfully', [
                'pairs_added' => count($matchings),
                'quiz_total_questions' => $quiz->fresh()->total_questions,
            ], 201);
        } catch (\Exception $e) {
            return BaseResponse::Error('Failed to add pairs: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete MCQ question
     */
    public function deleteMCQQuestion($questionId)
    {
        try {
            $mentorId = auth()->id();

            $question = $this->quizQuestionRepo->find($questionId);
            if (!$question) {
                return BaseResponse::Error('Question not found', 404);
            }

            $quiz = $question->quiz()->with('course')->first();

            if ($quiz->course->user_id !== $mentorId) {
                return BaseResponse::Error('Unauthorized access', 403);
            }

            $this->quizRepo->deleteMCQQuestion($questionId);

            return BaseResponse::Success('Question deleted successfully', null);
        } catch (\Exception $e) {
            return BaseResponse::Error('Failed to delete question: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete matching pair
     */
    public function deleteMatchingPair($matchingId)
    {
        try {
            $mentorId = auth()->id();

            $matching = $this->quizMatchingRepo->find($matchingId);
            if (!$matching) {
                return BaseResponse::Error('Matching pair not found', 404);
            }

            $quiz = $matching->quiz()->with('course')->first();

            if ($quiz->course->user_id !== $mentorId) {
                return BaseResponse::Error('Unauthorized access', 403);
            }

            $this->quizRepo->deleteMatchingQuestion($matchingId);

            return BaseResponse::Success('Matching pair deleted successfully', null);
        } catch (\Exception $e) {
            return BaseResponse::Error('Failed to delete pair: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get all quizzes for a course
     */
    public function quizzesByCourse($courseId)
    {
        $mentorId = auth()->id();

        $course = $this->courseRepo->find($courseId);
        if (!$course || $course->user_id !== $mentorId) {
            return BaseResponse::Error('Unauthorized course access', 403);
        }

        $quizzes = $this->quizRepo->getByCourse($courseId);

        return BaseResponse::Success('Course quizzes retrieved', $quizzes);
    }
}
