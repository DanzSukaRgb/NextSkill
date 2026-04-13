<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seeders dijalankan dalam urutan dependency
        $this->call([
            // 1. User data dulu
            UserSeeder::class,

            // 2. Categories dan Courses
            CategorySeeder::class,
            CourseSeeder::class,

            // 3. Contents: Lessons, Quizzes, Tasks
            LessonSeeder::class,
            QuizSeeder::class,
            TaskSeeder::class,

            // 4. Quiz Details
            QuizQuestionSeeder::class,
            QuizOptionSeeder::class,
            QuizMatchingSeeder::class,

            // 5. Student Enrollment & Progress
            EnrollmentSeeder::class,
            LessonProgressSeeder::class,
            TaskSubmissionSeeder::class,

            // 6. Quiz Attempts
            QuizAttemptSeeder::class,
            QuizAttemptAnswerSeeder::class,

            // 7. Achievements
            CertificateSeeder::class,

            // 8. Mentor-related data
            CourseMentorApplicationSeeder::class,
            MentorBalanceSeeder::class,

            // 9. Payment & Transaction data
            TransactionSeeder::class,

            // 10. User progression & points
            UserLevelSeeder::class,
            StudentPointSeeder::class,
        ]);
    }
}
