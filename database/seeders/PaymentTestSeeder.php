<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil atau buat Student
        $student = User::where('email', 'andi@gmail.com')->first();
        if (!$student) {
            $student = User::create([
                'name' => 'Andi Wijaya',
                'email' => 'andi@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'student',
            ]);
        }

        // 2. Ambil atau buat Mentor untuk kursus
        $mentor = User::where('role', 'mentor')->first();

        // 3. Buat Kategori
        $category = Category::firstOrCreate(['name' => 'Web Development']);

        // 4. Buat Kursus Berbayar
        $course = Course::create([
            'id' => Str::uuid(),
            'category_id' => $category->id,
            'user_id' => $mentor->id ?? null,
            'title' => 'Mastering Laravel Backend',
            'thumbnail' => 'laravel.png',
            'description' => 'Kursus intensif backend dengan Laravel dan Midtrans.',
            'level' => 'intermediate',
            'status' => 'published',
            'price' => 150000,
            'is_certificate' => true,
        ]);

        // 5. Buat Transaksi Pending (Untuk ngetes callback langsung tanpa hit checkout API)
        $transaction = Transaction::create([
            'id' => Str::uuid(),
            'user_id' => $student->id,
            'course_id' => $course->id,
            'gross_amount' => $course->price,
            'status' => 'pending',
            'snap_token' => 'dummy-snap-token-' . Str::random(10),
            'payment_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . Str::random(10),
        ]);

        $this->command->info('--- PAYMENT TEST DATA CREATED ---');
        $this->command->info('Student Email : ' . $student->email);
        $this->command->info('Course ID     : ' . $course->id);
        $this->command->info('Transaction ID: ' . $transaction->id);
        $this->command->info('Price         : ' . $course->price);
        $this->command->info('---------------------------------');
    }
}
