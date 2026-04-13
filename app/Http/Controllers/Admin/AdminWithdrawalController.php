<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\BaseResponse;
use App\Http\Controllers\Controller;
use App\Models\MentorBalance;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;

class AdminWithdrawalController extends Controller
{
    /**
     * Get all withdrawal requests with filters
     */
    public function index(Request $request)
    {
        try {
            $query = WithdrawalRequest::with(['user', 'approver']);

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by mentor
            if ($request->filled('mentor_id')) {
                $query->where('user_id', $request->mentor_id);
            }

            $withdrawals = $query->orderBy('requested_at', 'desc')->get();

            $data = $withdrawals->map(function ($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'mentor_id' => $withdrawal->user->id,
                    'mentor_name' => $withdrawal->user->name,
                    'mentor_email' => $withdrawal->user->email,
                    'amount' => $withdrawal->amount,
                    'formatted_amount' => 'Rp ' . number_format($withdrawal->amount, 0, ',', '.'),
                    'withdrawal_method' => $withdrawal->withdrawal_method,
                    'method_detail' => $withdrawal->withdrawal_method === 'bank'
                        ? [
                            'type' => 'bank',
                            'bank_name' => $withdrawal->bank_name,
                            'account_number' => $withdrawal->account_number,
                            'account_holder_name' => $withdrawal->account_holder_name,
                        ]
                        : [
                            'type' => 'e_wallet',
                            'e_wallet_type' => $withdrawal->e_wallet_type,
                            'e_wallet_number' => $withdrawal->e_wallet_number,
                        ],
                    'status' => $withdrawal->status,
                    'requested_at' => $withdrawal->requested_at,
                    'approved_at' => $withdrawal->approved_at,
                    'approved_by_name' => $withdrawal->approver?->name,
                    'rejection_reason' => $withdrawal->rejection_reason,
                ];
            });

            return BaseResponse::Success('Daftar penarikan berhasil diambil', ['withdrawals' => $data]);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil daftar penarikan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Approve withdrawal request
     */
    public function approve($withdrawalId)
    {
        try {
            $adminId = auth()->id();
            $withdrawal = WithdrawalRequest::find($withdrawalId);

            if (!$withdrawal) {
                return BaseResponse::Error('Permintaan penarikan tidak ditemukan', 404);
            }

            if ($withdrawal->status !== 'pending') {
                return BaseResponse::Error('Hanya permintaan pending yang bisa di-approve', 400);
            }

            // Get mentor's balance
            $balance = MentorBalance::where('user_id', $withdrawal->user_id)->first();

            if (!$balance || $balance->balance < $withdrawal->amount) {
                return BaseResponse::Error('Saldo mentor tidak cukup', 400);
            }

            // Update withdrawal status
            $withdrawal->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $adminId,
            ]);

            // Deduct from mentor's balance
            $balance->decrement('balance', $withdrawal->amount);

            return BaseResponse::Success('Penarikan berhasil di-approve dan saldo telah ditransfer', [
                'id' => $withdrawal->id,
                'status' => $withdrawal->status,
                'approved_at' => $withdrawal->approved_at,
            ]);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal approve penarikan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Reject withdrawal request
     */
    public function reject(Request $request, $withdrawalId)
    {
        try {
            $adminId = auth()->id();
            $withdrawal = WithdrawalRequest::find($withdrawalId);

            if (!$withdrawal) {
                return BaseResponse::Error('Permintaan penarikan tidak ditemukan', 404);
            }

            if ($withdrawal->status !== 'pending') {
                return BaseResponse::Error('Hanya permintaan pending yang bisa di-reject', 400);
            }

            $reason = $request->input('reason', 'Alasan tidak diberikan');

            // Update withdrawal status
            $withdrawal->update([
                'status' => 'rejected',
                'approved_at' => now(),
                'approved_by' => $adminId,
                'rejection_reason' => $reason,
            ]);

            return BaseResponse::Success('Penarikan berhasil di-reject', [
                'id' => $withdrawal->id,
                'status' => $withdrawal->status,
                'rejection_reason' => $withdrawal->rejection_reason,
            ]);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal reject penarikan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get withdrawal statistics
     */
    public function getStatistics()
    {
        try {
            $totalPending = WithdrawalRequest::where('status', 'pending')->sum('amount');
            $totalApproved = WithdrawalRequest::where('status', 'approved')->count();
            $totalRejected = WithdrawalRequest::where('status', 'rejected')->count();
            $totalCompleted = WithdrawalRequest::where('status', 'completed')->sum('amount');

            return BaseResponse::Success('Statistik penarikan berhasil diambil', [
                'pending_amount' => $totalPending,
                'formatted_pending' => 'Rp ' . number_format($totalPending, 0, ',', '.'),
                'approved_count' => $totalApproved,
                'rejected_count' => $totalRejected,
                'completed_amount' => $totalCompleted,
                'formatted_completed' => 'Rp ' . number_format($totalCompleted, 0, ',', '.'),
            ]);
        } catch (\Exception $e) {
            return BaseResponse::Error('Gagal mengambil statistik penarikan: ' . $e->getMessage(), 500);
        }
    }
}
