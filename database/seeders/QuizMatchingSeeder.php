<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizMatching;
use Illuminate\Database\Seeder;

class QuizMatchingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quizzes = Quiz::all();

        $matchingPairs = [
            ['Filter', 'Memproses elemen dengan kondisi tertentu'],
            ['Map', 'Mengubah setiap elemen dengan fungsi'],
            ['Reduce', 'Mengumpulkan nilai menjadi satu hasil'],
            ['Promise', 'Objek untuk operasi asynchronous'],
            ['Async/Await', 'Cara modern menangani pemrograman asinkron'],
            ['REST API', 'Arsitektur untuk komunikasi client-server'],
            ['JSON', 'Format pertukaran data yang ringan'],
            ['Authorization', 'Menentukan hak akses pengguna'],
        ];

        foreach ($quizzes as $quiz) {
            // Tambahkan 4-6 pasangan matching per quiz
            $pairsCount = rand(4, 6);
            $randomPairs = array_slice($matchingPairs, 0, $pairsCount);

            foreach ($randomPairs as $index => $pair) {
                QuizMatching::create([
                    'quiz_id' => $quiz->id,
                    'left_text' => $pair[0],
                    'right_text' => $pair[1],
                    'order' => $index + 1,
                ]);
            }
        }
    }
}
