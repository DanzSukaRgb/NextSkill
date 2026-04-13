<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::where('role', 'student')->get();
        $tasks = Task::all();

        $statuses = ['draft', 'submitted', 'graded'];

        foreach ($students as $student) {
            // Setiap student mensubmit 2-5 tugas
            $tasksToSubmit = $tasks->random(rand(2, 5));

            foreach ($tasksToSubmit as $task) {
                $status = $statuses[array_rand($statuses)];

                TaskSubmission::create([
                    'task_id' => $task->id,
                    'user_id' => $student->id,
                    'submission_link' => 'https://github.com/student-' . $student->id . '/task-' . $task->id,
                    'submission_text' => $this->generateSubmissionText(),
                    'submitted_at' => $status !== 'draft' ? now()->subDays(rand(1, 5)) : null,
                    'score' => $status === 'graded' ? rand(70, 100) : null,
                    'feedback' => $status === 'graded' ? 'Bagus! Silakan perbaiki beberapa hal kecil.' : null,
                    'status' => $status,
                ]);
            }
        }
    }

    private function generateSubmissionText(): string
    {
        return <<<EOI
            ## Submission Report

            ### Apa yang telah saya kerjakan:
            - Implementasi fitur utama sesuai requirement
            - Melakukan testing secara menyeluruh
            - Mengoptimalkan performa kode

            ### Tantangan yang dihadapi:
            - Debugging issue dengan database query
            - Optimasi struktur data

            ### Solusi yang diterapkan:
            - Menggunakan index untuk query optimization
            - Refactoring code structure
            - Menerapkan design pattern yang sesuai

            ### Waktu yang dihabiskan:
            - Development: 8 jam
            - Testing: 2 jam
            - Documentation: 1 jam

            EOI;
    }
}
