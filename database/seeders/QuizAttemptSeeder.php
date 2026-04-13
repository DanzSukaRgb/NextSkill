<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizAttempts;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuizAttemptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::where('role', 'student')->get();
        $quizzes = Quiz::all();

        foreach ($students as $student) {
            // Setiap student mencoba 3-6 quiz
            $quizzesToAttempt = $quizzes->random(rand(3, 6));

            foreach ($quizzesToAttempt as $quiz) {
                // Masing-masing quiz bisa dicoba 1-2 kali
                $attempts = rand(1, 2);

                for ($i = 0; $i < $attempts; $i++) {
                    $score = rand(50, 100);
                    $status = $score >= $quiz->minimum_score ? 'passed' : 'failed';
                    $startedAt = now()->subDays(rand(1, 60));

                    QuizAttempts::create([
                        'user_id' => $student->id,
                        'quiz_id' => $quiz->id,
                        'score' => $score,
                        'status' => $status,
                        'started_at' => $startedAt,
                        'submitted_at' => $startedAt->copy()->addMinutes(rand(10, 30)),
                    ]);
                }
            }
        }
    }
}
