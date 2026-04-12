<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\BaseResponse;
use App\Models\Quiz;
use App\Models\QuizAttempts;
use App\Models\QuizAttemptAnswers;
use App\Repositories\Leaderboard\LeaderboardRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    private $leaderboardRepo;

    public function __construct(LeaderboardRepository $leaderboardRepo)
    {
        $this->leaderboardRepo = $leaderboardRepo;
    }

    /**
     * Get quiz dengan questions dan matchings
     */
    public function show($quizId)
    {
        $quiz = Quiz::with(['questions.options', 'matchings'])
            ->find($quizId);

        if (!$quiz) {
            return BaseResponse::Error('Quiz not found', 404);
        }

        // Format response sesuai type
        $formattedQuiz = [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'instruction' => $quiz->instruction,
            'type' => $quiz->type,
            'quiz_scope' => $quiz->quiz_scope,
            'time_limit' => $quiz->time_limit,
            'minimum_score' => $quiz->minimum_score,
        ];

        if ($quiz->type === 'MCQ') {
            $formattedQuiz['questions'] = $quiz->questions->map(function ($q) {
                return [
                    'id' => $q->id,
                    'question' => $q->question,
                    'options' => $q->options->map(fn($o) => [
                        'id' => $o->id,
                        'option_text' => $o->option_text,
                    ]),
                ];
            });
        } elseif ($quiz->type === 'Matching') {
            $formattedQuiz['matchings'] = $quiz->matchings->map(function ($m) {
                return [
                    'id' => $m->id,
                    'left_text' => $m->left_text,
                    'right_text' => $m->right_text,
                    'order' => $m->order,
                ];
            });
        }

        return BaseResponse::Success('Quiz retrieved', $formattedQuiz);
    }

    /**
     * Get quiz attempts by student
     */
    public function getAttempts($quizId)
    {
        $studentId = auth()->id();

        $attempts = QuizAttempts::where('quiz_id', $quizId)
            ->where('user_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();

        return BaseResponse::Success('Quiz attempts retrieved', $attempts);
    }

    /**
     * Submit quiz answers
     */
    public function submitAnswers(Request $request, $quizId)
    {
        $studentId = auth()->id();
        $quiz = Quiz::find($quizId);

        if (!$quiz) {
            return BaseResponse::Error('Quiz not found', 404);
        }

        $answers = $request->input('answers');

        if (!$answers || !is_array($answers)) {
            return BaseResponse::Error('Invalid answers format', 400);
        }

        // Validate that all questions exist and belong to this quiz
        if ($quiz->type === 'MCQ') {
            foreach ($answers as $answer) {
                $questionId = $answer['question_id'] ?? null;
                if (!$questionId) {
                    return BaseResponse::Error('Invalid answer: missing question_id', 400);
                }

                $question = \App\Models\QuizQuestion::where('id', $questionId)
                    ->where('quiz_id', $quizId)
                    ->first();

                if (!$question) {
                    return BaseResponse::Error('Question ' . $questionId . ' not found in this quiz', 404);
                }
            }
        } elseif ($quiz->type === 'Matching') {
            foreach ($answers as $answer) {
                $matchingId = $answer['matching_id'] ?? null;
                if (!$matchingId) {
                    return BaseResponse::Error('Invalid answer: missing matching_id', 400);
                }

                $matching = \App\Models\QuizMatching::where('id', $matchingId)
                    ->where('quiz_id', $quizId)
                    ->first();

                if (!$matching) {
                    return BaseResponse::Error('Matching pair ' . $matchingId . ' not found in this quiz', 404);
                }
            }
        }

        try {
            DB::beginTransaction();

            $startedAt = now();
            $status = 'failed';

            $attempt = QuizAttempts::create([
                'user_id' => $studentId,
                'quiz_id' => $quizId,
                'started_at' => $startedAt,
                'submitted_at' => $startedAt,
                'status' => $status,
            ]);

            $score = $this->calculateScore($quiz, $answers, $attempt);
            $passing = $score >= $quiz->minimum_score;
            $finalStatus = $passing ? 'passed' : 'failed';

            $attempt->update([
                'score' => $score,
                'status' => $finalStatus,
            ]);

            if ($passing) {
                $points = intval(($score / 100) * 100);

                $this->leaderboardRepo->addPoints(
                    $studentId,
                    $points,
                    'quiz_' . $quiz->quiz_scope,
                    $quizId
                );
            }

            DB::commit();

            return BaseResponse::Success('Quiz submitted successfully', [
                'attempt_id' => $attempt->id,
                'score' => $score,
                'passing' => $passing,
                'points_earned' => $passing ? intval(($score / 100) * 100) : 0,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return BaseResponse::Error('Failed to submit quiz: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Calculate score
     */
    private function calculateScore(Quiz $quiz, $answers, QuizAttempts $attempt)
    {
        $correctCount = 0;
        $totalQuestions = count($answers);

        if ($quiz->type === 'MCQ') {
            foreach ($answers as $answer) {
                $questionId = $answer['question_id'] ?? null;
                $selectedOptionId = $answer['selected_option_id'] ?? null;

                if (!$questionId || !$selectedOptionId) {
                    continue;
                }

                $question = \App\Models\QuizQuestion::find($questionId);
                $isCorrect = false;

                if ($question && $question->correct_option_id == $selectedOptionId) {
                    $correctCount++;
                    $isCorrect = true;
                }

                QuizAttemptAnswers::create([
                    'quiz_attempt_id' => $attempt->id,
                    'quiz_question_id' => $questionId,
                    'answer_text' => $selectedOptionId,
                    'is_correct' => $isCorrect,
                ]);
            }
        } elseif ($quiz->type === 'Matching') {
            foreach ($answers as $answer) {
                $matchingId = $answer['matching_id'] ?? null;
                $selectedRightText = $answer['right_text'] ?? null;

                if (!$matchingId || !$selectedRightText) {
                    continue;
                }

                $matching = \App\Models\QuizMatching::find($matchingId);
                $isCorrect = false;

                if ($matching && $matching->right_text === $selectedRightText) {
                    $correctCount++;
                    $isCorrect = true;
                }

                QuizAttemptAnswers::create([
                    'quiz_attempt_id' => $attempt->id,
                    'matching_id' => $matchingId,
                    'answer_text' => $selectedRightText,
                    'is_correct' => $isCorrect,
                ]);
            }
        }

        $score = $totalQuestions > 0 ? ($correctCount / $totalQuestions) * 100 : 0;

        return round($score, 2);
    }

    /**
     * Get student quiz result
     */
    public function getResult($attemptId)
    {
        $studentId = auth()->id();

        $attempt = QuizAttempts::with(['answers', 'quiz'])
            ->where('id', $attemptId)
            ->where('user_id', $studentId)
            ->first();

        if (!$attempt) {
            return BaseResponse::Error('Attempt not found', 404);
        }

        return BaseResponse::Success('Quiz result retrieved', [
            'quiz_title' => $attempt->quiz->title,
            'score' => $attempt->score,
            'minimum_score' => $attempt->quiz->minimum_score,
            'passing' => $attempt->score >= $attempt->quiz->minimum_score,
            'submitted_at' => $attempt->submitted_at,
        ]);
    }
}
