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
            MentorBalance::create([
                'user_id' => $mentor->id,
                'balance' => rand(500000, 10000000) / 100, // Antara 50ribu - 100juta
            ]);
        }
    }
}
