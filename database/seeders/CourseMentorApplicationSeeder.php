<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseMentorApplication;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseMentorApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();
        $potentialMentors = User::where('role', 'student')->limit(3)->get();

        $statuses = ['pending', 'approved', 'rejected'];
        $motivations = [
            'Saya memiliki pengalaman luas di bidang ini dan ingin berbagi ilmu dengan komunitas.',
            'Saya bersemangat untuk membantu siswa memahami konsep-konsep yang kompleks.',
            'Saya memiliki passion dalam mengajar dan mentoring.',
            'Saya ingin berkontribusi kepada pengembangan kurikulum yang lebih baik.',
            'Saya percaya bahwa berbagi pengetahuan adalah cara terbaik untuk belajar.',
        ];

        foreach ($courses->take(5) as $course) {
            $applicantsCount = rand(1, 3);
            $selectedMentors = [];

            for ($i = 0; $i < $applicantsCount; $i++) {
                // Pastikan tidak ada duplikat mentor untuk kursus yang sama
                do {
                    $mentor = $potentialMentors->random();
                } while (in_array($mentor->id, $selectedMentors));

                $selectedMentors[] = $mentor->id;
                $status = $statuses[array_rand($statuses)];

                CourseMentorApplication::create([
                    'id' => Str::uuid(),
                    'course_id' => $course->id,
                    'user_id' => $mentor->id,
                    'status' => $status,
                    'motivation' => $motivations[array_rand($motivations)],
                    'rejection_reason' => $status === 'rejected'
                        ? 'Kami telah memilih mentor lain yang lebih sesuai untuk kursus ini.'
                        : null,
                ]);
            }
        }
    }
}
