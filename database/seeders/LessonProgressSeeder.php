<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Database\Seeder;

class LessonProgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::where('role', 'student')->get();
        $lessons = Lesson::all();

        foreach ($students as $student) {
            // Setiap student menyelesaikan 5-15 pelajaran
            $lessonsToComplete = $lessons->random(rand(5, 15));

            foreach ($lessonsToComplete as $lesson) {
                $isCompleted = rand(0, 1) === 1;

                LessonProgress::create([
                    'user_id' => $student->id,
                    'lesson_id' => $lesson->id,
                    'is_completed' => $isCompleted,
                    'completed_at' => $isCompleted ? now()->subDays(rand(1, 60)) : null,
                ]);
            }
        }
    }
}
