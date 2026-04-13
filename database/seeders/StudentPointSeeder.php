<?php

namespace Database\Seeders;

use App\Models\StudentPoint;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentPointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::where('role', 'student')->get();

        $pointSources = [
            'course_completion',
            'quiz_passed',
            'task_submission',
            'lesson_completed',
            'engagement_streak',
            'community_help',
        ];

        foreach ($students as $student) {
            // Setiap student mendapat 5-15 point entries
            $pointCount = rand(5, 15);

            for ($i = 0; $i < $pointCount; $i++) {
                StudentPoint::create([
                    'student_id' => $student->id,
                    'points_source' => $pointSources[array_rand($pointSources)],
                    'source_id' => rand(1, 100),
                    'points' => rand(10, 100),
                    'gained_at' => now()->subDays(rand(1, 90)),
                ]);
            }
        }
    }
}
