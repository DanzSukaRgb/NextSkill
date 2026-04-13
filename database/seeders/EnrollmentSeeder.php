<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::where('role', 'student')->get();
        $courses = Course::all();

        $statuses = ['active', 'completed', 'cancelled'];

        foreach ($students as $student) {
            // Setiap student mengambil 2-4 kursus
            $coursesToTake = $courses->random(rand(2, 4));

            foreach ($coursesToTake as $course) {
                $status = $statuses[array_rand($statuses)];

                Enrollment::create([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'enrolled_at' => now()->subDays(rand(1, 90)),
                    'status' => $status,
                    'progress_percentage' => $status === 'completed' ? 100 : rand(10, 90),
                ]);
            }
        }
    }
}
