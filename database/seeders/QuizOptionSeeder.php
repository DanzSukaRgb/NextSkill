<?php

namespace Database\Seeders;

use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class QuizOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = QuizQuestion::all();

        $optionSets = [
            ['Pilihan A', 'Pilihan B', 'Pilihan C', 'Pilihan D'],
            ['Konsep pertama', 'Konsep kedua', 'Konsep ketiga', 'Konsep keempat'],
            ['Metode A', 'Metode B', 'Metode C', 'Metode D'],
        ];

        foreach ($questions as $question) {
            if ($question->question_type === 'multiple_choice') {
                $options = $optionSets[array_rand($optionSets)];
                $correctAnswerIndex = rand(0, 3);

                foreach ($options as $index => $option) {
                    QuizOption::create([
                        'quiz_question_id' => $question->id,
                        'option_text' => $option,
                        'is_correct' => $index === $correctAnswerIndex,
                    ]);
                }
            } else {
                // True/False
                $correctAnswer = rand(0, 1) === 0 ? true : false;

                QuizOption::create([
                    'quiz_question_id' => $question->id,
                    'option_text' => 'Benar',
                    'is_correct' => $correctAnswer,
                ]);

                QuizOption::create([
                    'quiz_question_id' => $question->id,
                    'option_text' => 'Salah',
                    'is_correct' => !$correctAnswer,
                ]);
            }
        }
    }
}
