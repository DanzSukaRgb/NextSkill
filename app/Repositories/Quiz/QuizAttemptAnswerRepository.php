<?php

namespace App\Repositories\Quiz;

use App\Models\QuizAttemptAnswers;

class QuizAttemptAnswerRepository
{
    private $model;
    public function __construct(QuizAttemptAnswers $model)
    {
        $this->model = $model;
    }

    public function createAnswer($attemptId, $questionId, $selectedOptionId, $isCorrect)
    {
        return $this->model->create([
            'attempt_id' => $attemptId,
            'question_id' => $questionId,
            'selected_option_id' => $selectedOptionId,
            'is_correct' => $isCorrect,
        ]);
    }
}