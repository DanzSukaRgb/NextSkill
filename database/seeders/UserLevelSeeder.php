<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Database\Seeder;

class UserLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $level = rand(1, 10);
            $totalXp = $level * 1000 + rand(0, 999);

            UserLevel::create([
                'user_id' => $user->id,
                'level' => $level,
                'total_xp' => $totalXp,
                'xp_for_next_level' => ($level + 1) * 1000,
            ]);
        }
    }
}
