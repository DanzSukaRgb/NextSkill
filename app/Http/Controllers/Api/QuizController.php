<?php

namespace App\Http\Controllers\Api;

use App\Helpers\BaseResponse;
use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Repositories\Leaderboard\LeaderboardRepository;
use App\Repositories\Quiz\QuizAttemptRepository;
use App\Repositories\Quiz\QuizRepository;
use App\Services\Master\QuizService;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    private $leaderboardRepo;
    private $quizRepo;
    private $quizService;
    private $quizAttemptRepo;

    public function __construct(
        LeaderboardRepository $leaderboardRepo,
        QuizRepository $quizRepo,
        QuizService $quizService,
        QuizAttemptRepository $quizAttemptRepo
        )
    {
        $this->leaderboardRepo = $leaderboardRepo;
        $this->quizRepo = $quizRepo;
        $this->quizService = $quizService;
        $this->quizAttemptRepo = $quizAttemptRepo;
    }

    public function show($quizId)
    {
        $quiz = $this->quizRepo->findWithQuestions($quizId);

        if (!$quiz) {
            return BaseResponse::Error('Tidak ada quiz yang ditemukan', 404);
        }

        $formattedQuiz = $this->quizService->quizDetail($quiz);

        return BaseResponse::Success('Quiz Berhasil Diambil', $formattedQuiz);
    }

    /**
     * Get quiz attempts by student
     */
    public function getAttempts($quizId)
    {
        $studentId = auth()->id();

        $attempts = $this->quizAttemptRepo->getAttemptsByQuizAndUser($quizId, $studentId);

        return BaseResponse::Success('Quiz attempts retrieved', $attempts);
    }

    /**
     * Submit quiz answers
     */
    public function submitAnswers(Request $request, $quizId)
    {
        $studentId = auth()->id();
        $quiz = $this->quizRepo->find($quizId);

        if (!$quiz) {
            return BaseResponse::Error('Tidak ada quiz yang ditemukan', 404);
        }

        $answers = $request->input('answers');

        if (!$answers || !is_array($answers)) {
            return BaseResponse::Error('Format jawaban tidak valid', 400);
        }

        $result = $this->quizService->validateQuizAttempt($quiz, $answers);
        if (!$result) {
            return BaseResponse::Error('Gagal memproses jawaban', 500);
        }

        return BaseResponse::Success('Jawaban berhasil diproses', $result);
    }

    /**
     * Get student quiz result
     */
    public function getResult($attemptId)
    {
        $studentId = auth()->id();

        $attempt = $this->quizAttemptRepo->quizAttemptWithAnswersAndQuestions($attemptId, $studentId);

        if (!$attempt) {
            return BaseResponse::Error('Jawaban tidak ditemukan', 404);
        }

        return BaseResponse::Success('Quiz result retrieved', [
            'quiz_title' => $attempt->quiz->title,
            'score' => $attempt->score,
            'minimum_score' => $attempt->quiz->minimum_score,
            'passing' => $attempt->score >= $attempt->quiz->minimum_score,
            'submitted_at' => $attempt->submitted_at,
        ]);
    }

    /**
     * Get quizzes by course for student
     */
    public function getQuizzesByCourse($courseId)
    {
        $studentId = auth()->id();

        // Check if student is enrolled in the course
        $enrollment = \App\Models\Enrollment::where('user_id', $studentId)
            ->where('course_id', $courseId)
            ->first();

        if (!$enrollment) {
            return BaseResponse::Error('Anda belum terdaftar di kursus ini', 403);
        }

        $quizzes = $this->quizRepo->getByCourse($courseId);

        return BaseResponse::Success('Kuis kursus berhasil diambil', $quizzes);
    }
}
