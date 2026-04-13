<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::where('role', 'student')->get();
        $courses = Course::where('price', '>', 0)->get();

        $statuses = ['pending', 'success', 'failed', 'expired'];
        $invoiceNumber = 1000;

        foreach ($students as $student) {
            // Setiap student melakukan 1-3 transaksi
            $transactionCount = rand(1, 3);

            for ($i = 0; $i < $transactionCount; $i++) {
                $course = $courses->random();
                $status = $statuses[array_rand($statuses)];

                Transaction::create([
                    'id' => Str::uuid(),
                    'invoice_number' => 'INV-' . date('Ym') . '-' . str_pad($invoiceNumber, 6, '0', STR_PAD_LEFT),
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                    'gross_amount' => $course->price,
                    'status' => $status,
                    'snap_token' => 'token_' . Str::random(20),
                    'payment_url' => $status === 'pending'
                        ? 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . Str::random(10)
                        : null,
                ]);
                $invoiceNumber++;
            }
        }
    }
}
