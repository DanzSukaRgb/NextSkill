<?php

namespace Database\Seeders;

use App\Models\QuizAttempts;
use App\Models\QuizAttemptAnswers;
use Illuminate\Database\Seeder;

class QuizAttemptAnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quizAttempts = QuizAttempts::all();

        foreach ($quizAttempts as $attempt) {
            $quizQuestions = $attempt->quiz->questions;

            foreach ($quizQuestions as $question) {
                // Secara acak siswa menjawab benar atau salah
                $isCorrect = rand(0, 1) === 1;

                QuizAttemptAnswers::create([
                    'quiz_attempt_id' => $attempt->id,
                    'quiz_question_id' => $question->id,
                    'answer_text' => 'Jawaban ' . ($isCorrect ? 'Benar' : 'Salah'),
                    'is_correct' => $isCorrect,
                ]);
            }
        }
    }
}
