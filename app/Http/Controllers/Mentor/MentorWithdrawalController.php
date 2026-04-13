<?php

namespace App\Http\Controllers\Mentor;

use App\Helpers\BaseResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Withdrawal\WithdrawalRequest as WithdrawalFormRequest;
use App\Models\Course;
use App\Models\MentorBalance;
use App\Models\Transaction;
use App\Models\WithdrawalRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MentorWithdrawalController extends Controller
{
    /**
     * Get mentor's current balance
     */
    public function getBalance()
    {
        try {
            $userId = auth()->id();
            $balance = MentorBalance::where('user_id', $userId)->first();

            if (!$balance) {
                $balance = MentorBalance::create([
                    'user_id' => $userId,
                    'balance' => 0,
                ]);
            }

            return BaseResponse::Success('Saldo berhasil diambil', [
                'balance' => $balance->balance,
                'formatted_balance' => 'Rp ' . number_format($balance->balance, 0, ',', '.'),
            ]);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil saldo: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Request withdrawal
     */
    public function requestWithdrawal(WithdrawalFormRequest $request)
    {
        try {
            $userId = auth()->id();
            $amount = $request->input('amount');

            // Get mentor's balance
            $balance = MentorBalance::where('user_id', $userId)->first();

            if (!$balance || $balance->balance < $amount) {
                return BaseResponse::Error('Saldo tidak cukup untuk penarikan', 400);
            }

            // Create withdrawal request
            $withdrawalData = $request->validated();
            $withdrawalData['user_id'] = $userId;
            $withdrawalData['status'] = 'pending';
            $withdrawalData['requested_at'] = now();

            $withdrawal = WithdrawalRequest::create($withdrawalData);

            return BaseResponse::Success('Permintaan penarikan berhasil dibuat', [
                'id' => $withdrawal->id,
                'amount' => $withdrawal->amount,
                'status' => $withdrawal->status,
                'requested_at' => $withdrawal->requested_at,
            ], 201);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal membuat permintaan penarikan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get withdrawal history
     */
    public function getWithdrawalHistory(Request $request)
    {
        try {
            $userId = auth()->id();

            $query = WithdrawalRequest::where('user_id', $userId);

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $withdrawals = $query->orderBy('requested_at', 'desc')->get();

            $historyData = $withdrawals->map(function ($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'formatted_amount' => 'Rp ' . number_format($withdrawal->amount, 0, ',', '.'),
                    'withdrawal_method' => $withdrawal->withdrawal_method,
                    'method_detail' => $withdrawal->withdrawal_method === 'bank'
                        ? "{$withdrawal->bank_name} - {$withdrawal->account_number}"
                        : "{$withdrawal->e_wallet_type} - {$withdrawal->e_wallet_number}",
                    'status' => $withdrawal->status,
                    'requested_at' => $withdrawal->requested_at,
                    'approved_at' => $withdrawal->approved_at,
                    'rejection_reason' => $withdrawal->rejection_reason,
                ];
            });

            return BaseResponse::Success('Riwayat penarikan berhasil diambil', ['withdrawals' => $historyData]);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil riwayat penarikan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get income statistics with real data from last 6 months
     */
    public function getIncomeStatistics()
    {
        try {
            $userId = auth()->id();

            // Get current balance
            $balance = MentorBalance::where('user_id', $userId)->first();
            $currentBalance = $balance ? $balance->balance : 0;

            // Get total withdrawn (approved or completed)
            $totalWithdrawn = WithdrawalRequest::where('user_id', $userId)
                ->whereIn('status', ['completed', 'approved'])
                ->sum('amount');

            // Get pending withdrawal
            $pendingWithdrawal = WithdrawalRequest::where('user_id', $userId)
                ->where('status', 'pending')
                ->sum('amount');

            // Get all courses owned by mentor
            $courseIds = Course::where('user_id', $userId)->pluck('id');

            // Query transactions for mentor's courses (last 6 months)
            $sixMonthsAgo = Carbon::now()->subMonths(6)->startOfMonth();

            $transactions = Transaction::whereIn('course_id', $courseIds)
                ->where('status', 'success')
                ->where('created_at', '>=', $sixMonthsAgo)
                ->get();

            // Initialize monthly income data with 0 for last 6 months
            $monthlyData = [];
            $currentDate = Carbon::now()->startOfMonth();

            for ($i = 5; $i >= 0; $i--) {
                $date = $currentDate->copy()->subMonths($i);
                $monthNumber = $date->month;
                $year = $date->year;

                $monthlyData[] = [
                    'month' => $date->format('M'),
                    'month_number' => $monthNumber,
                    'year' => $year,
                    'income' => 0,
                ];
            }

            // Calculate mentor revenue share from transactions
            // Assuming RevenueShare config: mentor_revenue_share percentage
            // Or calculated as fixed amount per transaction
            foreach ($transactions as $transaction) {
                $transactionDate = Carbon::parse($transaction->created_at);
                $transactionMonth = $transactionDate->month;
                $transactionYear = $transactionDate->year;

                // Calculate mentor income (assuming 80% for mentor, 20% for platform)
                // Adjust this based on your RevenueShare model
                $mentorIncome = $transaction->gross_amount * 0.8; // 80% to mentor

                // Find and add to corresponding month
                foreach ($monthlyData as &$monthData) {
                    if ($monthData['month_number'] == $transactionMonth && $monthData['year'] == $transactionYear) {
                        $monthData['income'] += $mentorIncome;
                        break;
                    }
                }
            }

            // Remove unnecessary fields and format
            $monthlyData = array_map(function ($item) {
                return [
                    'month' => $item['month'],
                    'income' => intval($item['income']),
                ];
            }, $monthlyData);

            return BaseResponse::Success('Statistik pendapatan berhasil diambil', [
                'current_balance' => $currentBalance,
                'formatted_balance' => 'Rp ' . number_format($currentBalance, 0, ',', '.'),
                'total_withdrawn' => $totalWithdrawn,
                'formatted_withdrawn' => 'Rp ' . number_format($totalWithdrawn, 0, ',', '.'),
                'pending_withdrawal' => $pendingWithdrawal,
                'formatted_pending' => 'Rp ' . number_format($pendingWithdrawal, 0, ',', '.'),
                'monthly_income' => $monthlyData,
            ]);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil statistik pendapatan: ' . $e->getMessage(), 500);
        }
    }
}
