<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();

        $taskTitles = [
            'Praktik Implementasi Fitur',
            'Analisis dan Diskusi Kasus',
            'Proyek Mini Development',
            'Code Review dan Optimization',
            'Dokumentasi Teknis',
            'Presentasi Solusi',
            'Bug Fixing Challenge',
            'Design Pattern Implementation',
        ];

        foreach ($courses as $course) {
            // 3-5 tugas per kursus
            $taskCount = rand(3, 5);

            for ($i = 1; $i <= $taskCount; $i++) {
                $taskScope = $i === $taskCount ? 'final' : 'lesson';
                $lesson = $course->lessons()->inRandomOrder()->first();

                Task::create([
                    'course_id' => $course->id,
                    'lesson_id' => $lesson ? $lesson->id : null,
                    'title' => $taskTitles[array_rand($taskTitles)].' #'.$i,
                    'description' => 'Selesaikan tugas ini dengan mengikuti instruksi yang telah diberikan. '
                        .'Tugas ini dirancang untuk memperkuat pemahaman Anda tentang konsep dalam kursus.',
                    'instruction' => $this->generateInstruction(),
                    'due_date' => now()->addDays(rand(7, 30)),
                    'task_scope' => $taskScope,
                ]);
            }
        }
    }

    private function generateInstruction(): string
    {
        return <<<'EOI'
            ## Instruksi Tugas

            1. Baca dan pahami semua requirement dengan seksama
            2. Ikuti standar code yang telah ditentukan
            3. Buat submission dalam format yang sesuai
            4. Sertakan dokumentasi yang jelas
            5. Submit sebelum deadline yang ditentukan

            ### Kriteria Penilaian:
            - ✓ Fungsionalitas: 40%
            - ✓ Code Quality: 30%
            - ✓ Documentation: 20%
            - ✓ Delivery On Time: 10%
            EOI;
    }
}
