<?php

namespace App\Repositories\Quiz;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use App\Models\QuizMatching;
use Illuminate\Support\Facades\DB;

class QuizRepository
{
    private $model;
    
    public function __construct(Quiz $model)
    {
        $this->model = $model;
    }
    /**
     * Create new quiz
     */
    public function create(array $data)
    {
        return Quiz::create($data);
    }

    /**
     * Update quiz
     */
    public function update($quizId, array $data)
    {
        $quiz = Quiz::find($quizId);
        if ($quiz) {
            $quiz->update($data);
        }
        return $quiz;
    }

    public function find($quizId)
    {
        return $this->model->find($quizId);
    }

    /**
     * Get quiz by id with relationships
     */
    public function findWithQuestions($quizId)
    {
        return $this->model->with(['questions.options', 'matchings'])->find($quizId);
    }

    /**
     * Get quizzes by course
     */
    public function getByCourse($courseId)
    {
        return $this->model->where('course_id', $courseId)
            ->with(['lesson', 'questions', 'matchings'])
            ->get();
    }

    public function quizWithCourseAndLesson($quizId)
    {
        return $this->model->with(['course', 'lesson','questions.options','matchings'])->find($quizId);
    }

    /**
     * Delete quiz with all related questions
     */
    public function delete($quizId)
    {
        return DB::transaction(function () use ($quizId) {
            QuizQuestion::where('quiz_id', $quizId)->delete();
            QuizMatching::where('quiz_id', $quizId)->delete();
            return $this->model->find($quizId)->delete();
        });
    }

    /**
     * Add MCQ questions with options
     */
    public function addMCQQuestions($quizId, array $questions)
    {
        return DB::transaction(function () use ($quizId, $questions) {
            $createdQuestions = [];

            foreach ($questions as $index => $question) {
                $q = QuizQuestion::create([
                    'quiz_id' => $quizId,
                    'question' => $question['question'],
                    'order_number' => $index + 1,
                ]);

                $correctOptionId = null;
                foreach ($question['options'] as $optIndex => $option) {
                    $opt = QuizOption::create([
                        'quiz_question_id' => $q->id,
                        'option_text' => $option['text'],
                    ]);

                    if (isset($option['is_correct']) && $option['is_correct']) {
                        $correctOptionId = $opt->id;
                    }
                }

                $q->update(['correct_option_id' => $correctOptionId]);
                $createdQuestions[] = $q;
            }

            $quiz = Quiz::find($quizId);
            $quiz->update(['total_questions' => $quiz->questions()->count()]);

            return $createdQuestions;
        });
    }

    /**
     * Add matching questions
     */
    public function addMatchingQuestions($quizId, array $pairs)
    {
        return DB::transaction(function () use ($quizId, $pairs) {
            $createdMatchings = [];

            foreach ($pairs as $index => $pair) {
                $matching = QuizMatching::create([
                    'quiz_id' => $quizId,
                    'left_text' => $pair['left_text'],
                    'right_text' => $pair['right_text'],
                    'order' => $index + 1,
                ]);

                $createdMatchings[] = $matching;
            }

            $quiz = Quiz::find($quizId);
            $quiz->update(['total_questions' => $quiz->matchings()->count()]);

            return $createdMatchings;
        });
    }

    /**
     * Update MCQ question
     */
    public function updateMCQQuestion($questionId, array $data)
    {
        return DB::transaction(function () use ($questionId, $data) {
            $question = QuizQuestion::find($questionId);
            $question->update(['question' => $data['question']]);

            QuizOption::where('quiz_question_id', $questionId)->delete();

            $correctOptionId = null;
            foreach ($data['options'] as $option) {
                $opt = QuizOption::create([
                    'quiz_question_id' => $questionId,
                    'option_text' => $option['text'],
                ]);

                if (isset($option['is_correct']) && $option['is_correct']) {
                    $correctOptionId = $opt->id;
                }
            }

            $question->update(['correct_option_id' => $correctOptionId]);

            return $question;
        });
    }

    /**
     * Update matching question
     */
    public function updateMatchingQuestion($matchingId, array $data)
    {
        $matching = QuizMatching::find($matchingId);
        return $matching->update([
            'left_text' => $data['left_text'],
            'right_text' => $data['right_text'],
        ]);
    }

    /**
     * Delete MCQ question
     */
    public function deleteMCQQuestion($questionId)
    {
        return DB::transaction(function () use ($questionId) {
            $question = QuizQuestion::find($questionId);
            $quizId = $question->quiz_id;

            QuizOption::where('quiz_question_id', $questionId)->delete();
            $question->delete();

            // Update quiz total_questions
            $quiz = Quiz::find($quizId);
            $quiz->update(['total_questions' => $quiz->questions()->count()]);
        });
    }

    /**
     * Delete matching question
     */
    public function deleteMatchingQuestion($matchingId)
    {
        $matching = QuizMatching::find($matchingId);
        $quizId = $matching->quiz_id;

        $matching->delete();

        // Update quiz total_questions
        $quiz = Quiz::find($quizId);
        $quiz->update(['total_questions' => $quiz->matchings()->count()]);
    }
}
