<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'bio' => 'Administrator platform',
        ]);

        // Create Mentor User
        User::create([
            'name' => 'Mentor Profesional',
            'email' => 'mentor@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'mentor',
            'bio' => 'Mentor berpengalaman dalam pengajaran',
        ]);

        // Create Multiple Student Users
        $students = [
            [
                'name' => 'Andi Wijaya',
                'email' => 'andi@gmail.com',
                'bio' => 'Siswa perempuan bersemangat belajar programming',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@gmail.com',
                'bio' => 'Siswa yang ingin menguasai web development',
            ],
            [
                'name' => 'Citra Dewi',
                'email' => 'citra@gmail.com',
                'bio' => 'Siswa fokus pada mobile development',
            ],
            [
                'name' => 'Dono Santoso',
                'email' => 'dono@gmail.com',
                'bio' => 'Siswa pemula yang antusias',
            ],
            [
                'name' => 'Eka Putri',
                'email' => 'eka@gmail.com',
                'bio' => 'Siswa dengan minat pada data science',
            ],
        ];

        foreach ($students as $student) {
            User::create([
                'name' => $student['name'],
                'email' => $student['email'],
                'password' => Hash::make('password'),
                'role' => 'student',
                'bio' => $student['bio'],
            ]);
        }
    }
}
