<?php

namespace App\Services\Master;

use App\Models\Quiz;
use App\Models\QuizAttempts;
use App\Repositories\Leaderboard\LeaderboardRepository;
use App\Repositories\Quiz\QuizAttemptAnswerRepository;
use App\Repositories\Quiz\QuizAttemptRepository;
use App\Repositories\Quiz\QuizMatchingRepository;
use App\Repositories\Quiz\QuizQuestionRepository;
use App\Repositories\Quiz\QuizRepository;
use Illuminate\Support\Facades\DB;

class QuizService
{
    private $quizRepo;
    private $quizAttemptRepo;
    private $leaderboardRepo;
    private $quizQuestionRepo;
    private $quizAttemptAnswerRepo;
    private $quizMatchingRepo;
    public function __construct(
        QuizRepository $quizRepo,
        QuizAttemptRepository $quizAttemptRepo,
        LeaderboardRepository $leaderboardRepository,
        QuizQuestionRepository $quizQuestionRepo,
        QuizAttemptAnswerRepository $quizAttemptAnswerRepo,
        QuizMatchingRepository $quizMatchingRepo
    ) 
    {
        $this->quizRepo = $quizRepo;
        $this->quizAttemptRepo = $quizAttemptRepo;
        $this->leaderboardRepo = $leaderboardRepository;
        $this->quizQuestionRepo = $quizQuestionRepo;
        $this->quizAttemptAnswerRepo = $quizAttemptAnswerRepo;
        $this->quizMatchingRepo = $quizMatchingRepo;
    }

    private function quizMapping($quiz)
    {
        return [
            'id' => $quiz->id,
            'course_id' => $quiz->course_id,
            'lesson_id' => $quiz->lesson_id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'instruction' => $quiz->instruction,
            'time_limit' => $quiz->time_limit,
            'minimum_score' => $quiz->minimum_score,
            'type' => $quiz->type,
            'quiz_scope' => $quiz->quiz_scope,
            'total_questions' => $quiz->total_questions,
        ];
    }

    public function quizDetail($quiz)
    {
        $quizMapping = $this->quizMapping($quiz);

        if($quiz->type === "MCQ") {
            $quizMapping['questions'] = $quiz->questions->map(function ($q) {
                return [
                    'id' => $q->id,
                    'question' => $q->question,
                    'options' => $q->options->map(fn($o) => [
                        'id' => $o->id,
                        'option_text' => $o->option_text,
                    ]),
                ];
            });
        } else {
            $quizMapping['matchings'] = $quiz->matchings->map(function ($m) {
                return [
                    'id' => $m->id,
                    'left_text' => $m->left_text,
                    'right_text' => $m->right_text,
                    'order' => $m->order,
                ];
            });
        }

        return $quizMapping;
    }

    public function validateQuizAttempt($quiz, $answers)
    {
        if($quiz->type === 'MCQ') {
            foreach ($answers as $answer) {
                $answerId = $answer['question_id'] ?? null;
                if (!$answerId) {
                    return false;
                }

                $quizQuestion = $quiz->questions->where('id', $answerId)->first();
                if (!$quizQuestion) {
                    return false;
                }
            }
        } elseif ($quiz->type === 'Matching') {
            foreach ($answers as $answer) {
                $matchingId = $answer['matching_id'] ?? null;
                if (!$matchingId) {
                    return false;
                }

                $quizMatching = $quiz->matchings->where('id', $matchingId)->first();
                if (!$quizMatching) {
                    return false;
                }
            }
        }

        DB::beginTransaction();
        try{
            $startedAt = now();
            $status = 'failed';

            $attempt = $this->quizAttemptRepo->createAttempt($quiz->id, auth()->id(), $startedAt, $status);
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
                    auth()->id(),
                    $points,
                    'quiz_' . $quiz->quiz_scope,
                    $quiz->id
                );
            }
            DB::commit();
            return [
                'attempt_id' => $attempt->id,
                'score' => $score,
                'passing' => $passing,
                'points_earned' => $passing ? intval(($score / 100) * 100) : 0,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }        return true;
    }

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

                $question = $this->quizQuestionRepo->find($questionId);
                $isCorrect = false;

                if ($question && $question->correct_option_id == $selectedOptionId) {
                    $correctCount++;
                    $isCorrect = true;
                }

                $this->quizAttemptAnswerRepo->createAnswer($attempt->id, $questionId, $selectedOptionId, $isCorrect);
            }
        } elseif ($quiz->type === 'Matching') {
            foreach ($answers as $answer) {
                $matchingId = $answer['matching_id'] ?? null;
                $selectedRightText = $answer['right_text'] ?? null;

                if (!$matchingId || !$selectedRightText) {
                    continue;
                }

                $matching = $this->quizMatchingRepo->find($matchingId);
                $isCorrect = false;

                if ($matching && $matching->right_text === $selectedRightText) {
                    $correctCount++;
                    $isCorrect = true;
                }

                $this->quizAttemptAnswerRepo->createAnswer($attempt->id, $matchingId, $selectedRightText, $isCorrect);
            }
        }

        $score = $totalQuestions > 0 ? ($correctCount / $totalQuestions) * 100 : 0;

        return round($score, 2);
    }
}