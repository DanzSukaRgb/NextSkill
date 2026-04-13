<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LessonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();

        foreach ($courses as $course) {
            // 3-5 pelajaran per kursus
            $lessonsPerCourse = rand(3, 5);

            for ($i = 1; $i <= $lessonsPerCourse; $i++) {
                Lesson::create([
                    'id' => Str::uuid(),
                    'course_id' => $course->id,
                    'title' => $course->title . ' - Part ' . $i,
                    'content' => $this->generateContent('Pelajaran ' . $i . ' dari ' . $course->title),
                    'vidio_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                    'file_path' => '/lessons/' . Str::slug($course->title) . '/part-' . $i . '.pdf',
                    'order_number' => $i,
                    'duration_in_minutes' => rand(15, 60),
                    'is_preview' => $i === 1 ? true : false,
                ]);
            }
        }
    }

    private function generateContent($title): string
    {
        return <<<EOI
            <h2>$title</h2>
            <p>Dalam pelajaran ini, Anda akan mempelajari konsep-konsep penting dan praktik terbaik.</p>
            <h3>Topik yang dibahas:</h3>
            <ul>
                <li>Pengenalan dasar</li>
                <li>Konsep fundamental</li>
                <li>Praktik implementasi</li>
                <li>Best practices</li>
                <li>Studi kasus real-world</li>
            </ul>
            <p>Pastikan untuk mengikuti setiap langkah dengan teliti dan praktik dengan contoh yang disediakan.</p>
            EOI;
    }
}
