<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();

        foreach ($courses as $course) {
            // 2 quiz per kursus
            for ($i = 1; $i <= 2; $i++) {
                Quiz::create([
                    'course_id' => $course->id,
                    'title' => $course->title . ' - Quiz ' . $i,
                    'description' => 'Kuis untuk menguji pemahaman Anda tentang ' . $course->title,
                    'instruction' => 'Jawab semua pertanyaan dengan hati-hati. Anda memiliki 30 menit untuk menyelesaikan kuis ini.',
                    'time_limit' => 30,
                    'minimum_score' => 70,
                ]);
            }
        }
    }
}
