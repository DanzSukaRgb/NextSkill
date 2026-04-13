<?php

namespace App\Repositories\Quiz;

use App\Models\QuizQuestion;

class QuizQuestionRepository
{
    private $model;
    public function __construct(QuizQuestion $model)
    {
        $this->model = $model;
    }
    
    public function find($questionId)
    {
        return $this->model->find($questionId);
    }

}