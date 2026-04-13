<?php

namespace App\Repositories\Quiz;

use App\Models\QuizAttempts;

class QuizAttemptRepository
{
    private $model;
    public function __construct(QuizAttempts $model)
    {
        $this->model = $model;
    }

    public function getAttemptsByQuizAndUser($quizId, $userId)
    {
        return $this->model->where('quiz_id', $quizId)
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function createAttempt($quizId, $studentId, $startedAt, $status)
    {
        return $this->model->create([
            'user_id' => $studentId,
            'quiz_id' => $quizId,
            'started_at' => $startedAt,
            'submitted_at' => $startedAt,
            'status' => $status,
        ]);
    }

    public function quizAttemptWithAnswersAndQuestions($attemptId, $userId)
    {
        return $this->model->with(['answers', 'quiz'])->where('id', $attemptId)->where('user_id', $userId)->first();
    }

}   