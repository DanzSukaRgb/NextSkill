<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WithdrawalRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add withdrawal history for existing mentor (mentor@gmail.com)
        $existingMentor = User::where('email', 'mentor@gmail.com')->first();
        if ($existingMentor) {
            $existingWithdrawals = [
                [
                    'amount' => 1000000,
                    'withdrawal_method' => 'bank',
                    'bank_name' => 'BCA',
                    'account_number' => '1234567890',
                    'account_holder_name' => 'Mentor Profesional',
                    'e_wallet_type' => null,
                    'e_wallet_number' => null,
                    'status' => 'approved',
                    'requested_at' => now()->subDays(45),
                    'approved_at' => now()->subDays(44),
                    'approved_by' => 1,
                ],
                [
                    'amount' => 1000000,
                    'withdrawal_method' => 'bank',
                    'bank_name' => 'BCA',
                    'account_number' => '1234567890',
                    'account_holder_name' => 'Mentor Profesional',
                    'e_wallet_type' => null,
                    'e_wallet_number' => null,
                    'status' => 'approved',
                    'requested_at' => now()->subDays(35),
                    'approved_at' => now()->subDays(34),
                    'approved_by' => 1,
                ],
                [
                    'amount' => 1000000,
                    'withdrawal_method' => 'e_wallet',
                    'bank_name' => null,
                    'account_number' => null,
                    'account_holder_name' => null,
                    'e_wallet_type' => 'gopay',
                    'e_wallet_number' => '081234567890',
                    'status' => 'approved',
                    'requested_at' => now()->subDays(20),
                    'approved_at' => now()->subDays(19),
                    'approved_by' => 1,
                ],
                [
                    'amount' => 1000000,
                    'withdrawal_method' => 'bank',
                    'bank_name' => 'BCA',
                    'account_number' => '1234567890',
                    'account_holder_name' => 'Mentor Profesional',
                    'e_wallet_type' => null,
                    'e_wallet_number' => null,
                    'status' => 'pending',
                    'requested_at' => now()->subDays(1),
                    'approved_at' => null,
                    'approved_by' => null,
                ],
            ];

            foreach ($existingWithdrawals as $withdrawalData) {
                WithdrawalRequest::create(array_merge(
                    $withdrawalData,
                    ['user_id' => $existingMentor->id]
                ));
            }
        }

        // Create Multiple Mentor Users with Large Balances
        $mentors = [
            [
                'name' => 'Mentor Budi Santoso',
                'email' => 'mentor.budi@gmail.com',
                'bio' => 'Senior Mentor - Web Development',
                'withdrawals' => [
                    [
                        'amount' => 1000000,
                        'withdrawal_method' => 'bank',
                        'bank_name' => 'BCA',
                        'account_number' => '1234567890',
                        'account_holder_name' => 'Budi Santoso',
                        'e_wallet_type' => null,
                        'e_wallet_number' => null,
                        'status' => 'approved',
                        'requested_at' => now()->subDays(10),
                        'approved_at' => now()->subDays(9),
                        'approved_by' => 1,
                    ],
                    [
                        'amount' => 1000000,
                        'withdrawal_method' => 'bank',
                        'bank_name' => 'BCA',
                        'account_number' => '1234567890',
                        'account_holder_name' => 'Budi Santoso',
                        'e_wallet_type' => null,
                        'e_wallet_number' => null,
                        'status' => 'pending',
                        'requested_at' => now()->subDays(2),
                        'approved_at' => null,
                        'approved_by' => null,
                    ],
                ],
            ],
            [
                'name' => 'Mentor Siti Nurhaliza',
                'email' => 'mentor.siti@gmail.com',
                'bio' => 'Expert Mentor - Mobile Development',
                'withdrawals' => [
                    [
                        'amount' => 1500000,
                        'withdrawal_method' => 'e_wallet',
                        'bank_name' => null,
                        'account_number' => null,
                        'account_holder_name' => null,
                        'e_wallet_type' => 'gopay',
                        'e_wallet_number' => '081234567890',
                        'status' => 'approved',
                        'requested_at' => now()->subDays(15),
                        'approved_at' => now()->subDays(14),
                        'approved_by' => 1,
                    ],
                    [
                        'amount' => 1000000,
                        'withdrawal_method' => 'e_wallet',
                        'bank_name' => null,
                        'account_number' => null,
                        'account_holder_name' => null,
                        'e_wallet_type' => 'gopay',
                        'e_wallet_number' => '081234567890',
                        'status' => 'pending',
                        'requested_at' => now(),
                        'approved_at' => null,
                        'approved_by' => null,
                    ],
                ],
            ],
            [
                'name' => 'Mentor Ahmad Rizki',
                'email' => 'mentor.ahmad@gmail.com',
                'bio' => 'Specialist Mentor - Data Science',
                'withdrawals' => [
                    [
                        'amount' => 2000000,
                        'withdrawal_method' => 'bank',
                        'bank_name' => 'Mandiri',
                        'account_number' => '9876543210',
                        'account_holder_name' => 'Ahmad Rizki',
                        'e_wallet_type' => null,
                        'e_wallet_number' => null,
                        'status' => 'approved',
                        'requested_at' => now()->subDays(20),
                        'approved_at' => now()->subDays(19),
                        'approved_by' => 1,
                    ],
                    [
                        'amount' => 1500000,
                        'withdrawal_method' => 'bank',
                        'bank_name' => 'Mandiri',
                        'account_number' => '9876543210',
                        'account_holder_name' => 'Ahmad Rizki',
                        'e_wallet_type' => null,
                        'e_wallet_number' => null,
                        'status' => 'pending',
                        'requested_at' => now()->subDays(5),
                        'approved_at' => null,
                        'approved_by' => null,
                    ],
                ],
            ],
            [
                'name' => 'Mentor Dewi Lestari',
                'email' => 'mentor.dewi@gmail.com',
                'bio' => 'Master Mentor - UI/UX Design',
                'withdrawals' => [
                    [
                        'amount' => 3000000,
                        'withdrawal_method' => 'e_wallet',
                        'bank_name' => null,
                        'account_number' => null,
                        'account_holder_name' => null,
                        'e_wallet_type' => 'ovo',
                        'e_wallet_number' => '082345678901',
                        'status' => 'approved',
                        'requested_at' => now()->subDays(25),
                        'approved_at' => now()->subDays(24),
                        'approved_by' => 1,
                    ],
                    [
                        'amount' => 2500000,
                        'withdrawal_method' => 'e_wallet',
                        'bank_name' => null,
                        'account_number' => null,
                        'account_holder_name' => null,
                        'e_wallet_type' => 'ovo',
                        'e_wallet_number' => '082345678901',
                        'status' => 'pending',
                        'requested_at' => now()->subDays(1),
                        'approved_at' => null,
                        'approved_by' => null,
                    ],
                ],
            ],
            [
                'name' => 'Mentor Riko Hermawan',
                'email' => 'mentor.riko@gmail.com',
                'bio' => 'Professional Mentor - Backend Development',
                'withdrawals' => [
                    [
                        'amount' => 1800000,
                        'withdrawal_method' => 'bank',
                        'bank_name' => 'BRI',
                        'account_number' => '5555666677',
                        'account_holder_name' => 'Riko Hermawan',
                        'e_wallet_type' => null,
                        'e_wallet_number' => null,
                        'status' => 'approved',
                        'requested_at' => now()->subDays(30),
                        'approved_at' => now()->subDays(29),
                        'approved_by' => 1,
                    ],
                    [
                        'amount' => 1200000,
                        'withdrawal_method' => 'bank',
                        'bank_name' => 'BRI',
                        'account_number' => '5555666677',
                        'account_holder_name' => 'Riko Hermawan',
                        'e_wallet_type' => null,
                        'e_wallet_number' => null,
                        'status' => 'pending',
                        'requested_at' => now()->subDays(3),
                        'approved_at' => null,
                        'approved_by' => null,
                    ],
                ],
            ],
        ];

        foreach ($mentors as $mentorData) {
            // Create mentor user
            $mentor = User::create([
                'name' => $mentorData['name'],
                'email' => $mentorData['email'],
                'password' => Hash::make('password'),
                'role' => 'mentor',
                'bio' => $mentorData['bio'],
            ]);

            // Create withdrawal requests for this mentor
            foreach ($mentorData['withdrawals'] as $withdrawalData) {
                WithdrawalRequest::create(array_merge(
                    $withdrawalData,
                    ['user_id' => $mentor->id]
                ));
            }
        }
    }
}
