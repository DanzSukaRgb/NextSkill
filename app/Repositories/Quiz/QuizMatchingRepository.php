<?php

namespace App\Repositories\Quiz;

use App\Models\QuizMatching;

class QuizMatchingRepository
{
    private $model;
    
    public function __construct(QuizMatching $model)
    {
        $this->model = $model;
    }

    public function find($matchingId)
    {
        return $this->model->find($matchingId);
    }
}