<?php

namespace Database\Seeders;

use App\Models\MentorBalance;
use App\Models\User;
use Illuminate\Database\Seeder;

class MentorBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mentors = User::where('role', 'mentor')->get();

        foreach ($mentors as $mentor) {
            // Set balance 1 juta specifically for the main mentor (mentor@gmail.com)
            if ($mentor->email === 'mentor@gmail.com') {
                $balance = 1000000;
            } else {
                $balance = rand(500000, 10000000) / 100; // Antara 50ribu - 100juta
            }

            MentorBalance::create([
                'user_id' => $mentor->id,
                'balance' => $balance,
            ]);
        }
    }
}
