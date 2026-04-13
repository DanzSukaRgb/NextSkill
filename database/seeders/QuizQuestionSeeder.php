<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Database\Seeder;

class QuizQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quizzes = Quiz::all();

        $questionTemplates = [
            'Apa definisi dari ',
            'Bagaimana cara membuat ',
            'Jelaskan perbedaan antara ',
            'Apa keuntungan menggunakan ',
            'Dalam konteks ini, apa arti dari ',
        ];

        foreach ($quizzes as $quiz) {
            // 8-10 pertanyaan per quiz
            $questionCount = rand(8, 10);

            for ($i = 1; $i <= $questionCount; $i++) {
                $type = $i % 2 === 0 ? 'true_false' : 'multiple_choice';

                QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question' => $questionTemplates[array_rand($questionTemplates)] . $quiz->title . '? #' . $i,
                    'question_type' => $type,
                    'order_number' => $i,
                ]);
            }
        }
    }
}
