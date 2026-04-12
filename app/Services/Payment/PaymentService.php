<?php

namespace App\Services\Payment;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Payment\TransactionRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class PaymentService
{
    private $transactionRepo;

    public function __construct(TransactionRepository $transactionRepo)
    {
        $this->transactionRepo = $transactionRepo;

        // Setup Midtrans Configuration
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function checkout(Course $course, User $user): array
    {
        $transactionId = Str::uuid()->toString();
        $invoiceNumber = $this->generateInvoiceNumber();

        $transaction = $this->transactionRepo->create([
            'id' => $transactionId,
            'invoice_number' => $invoiceNumber,
            'user_id' => $user->id,
            'course_id' => $course->id,
            'gross_amount' => $course->price,
            'status' => 'pending',
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $transactionId,
                'gross_amount' => $course->price,
            ],
            'customer_details' => [
                'first_name' => $user->username ?? 'Student',
                'email' => $user->email,
            ],
            'item_details' => [
                [
                    'id' => $course->id,
                    'price' => $course->price,
                    'quantity' => 1,
                    'name' => mb_substr($course->title, 0, 50),
                ],
            ]
        ];

        $midtransResponse = Snap::createTransaction($params);
        $snapToken = $midtransResponse->token;
        $paymentUrl = $midtransResponse->redirect_url;
        
        $this->transactionRepo->update($transactionId, [
            'snap_token' => $snapToken,
            'payment_url' => $paymentUrl,
        ]);

        $simulationPayload = null;
        if (!config('services.midtrans.is_production', false)) {
            $simulationPayload = [
                'order_id' => $transactionId,
                'status_code' => '200',
                'gross_amount' => (string) $course->price,
                'signature_key' => hash('sha512', $transactionId . '200' . $course->price . config('services.midtrans.server_key')),
                'transaction_status' => 'settlement',
                'fraud_status' => 'accept',
                'transaction_id' => Str::uuid()->toString(),
                'payment_type' => 'bank_transfer',
            ];
        }

        return [
            'status' => 'success',
            'snap_token' => $snapToken,
            'payment_url' => $paymentUrl,
            'simulation_data' => $simulationPayload,
        ];
    }

    public function enrollFreeCourse(Course $course, User $user): array
    {
        // Prevent duplicate enrollment
        $exists = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($exists) {
            return ['status' => 'error', 'code' => 400, 'message' => 'Sudah terdaftar di kursus ini'];
        }

        Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
            'status' => 'active',
            'progress_percentage' => 0,
        ]);

        return ['status' => 'success', 'code' => 200, 'message' => 'Berhasil mendaftar (Kursus Gratis)'];
    }

    public function handleCallback(array $payload): array
    {
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $serverKey = config('services.midtrans.server_key');
        $signatureKey = $payload['signature_key'] ?? '';

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($expectedSignature !== $signatureKey) {
            Log::warning('Midtrans Invalid Signature Call', $payload);
            return ['status' => 'error', 'code' => 403, 'message' => 'Invalid signature'];
        }

        $transaction = $this->transactionRepo->findById($orderId);
        if (!$transaction) {
            return ['status' => 'error', 'code' => 404, 'message' => 'Transaction not found'];
        }

        // BRUTAL CHECK: Ensure paid amount matches our records to prevent price manipulation
        if ((int)$grossAmount !== (int)$transaction->gross_amount) {
            Log::critical('MIDTRANS PRICE MISMATCH DETECTED', [
                'order_id' => $orderId,
                'db_amount' => $transaction->gross_amount,
                'paid_amount' => $grossAmount
            ]);
            return ['status' => 'error', 'code' => 400, 'message' => 'Gross amount mismatch'];
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';

        $isSuccess = false;

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                $isSuccess = true;
            }
        } elseif ($transactionStatus == 'settlement') {
            $isSuccess = true;
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $this->transactionRepo->update($orderId, ['status' => 'failed']);
        } elseif ($transactionStatus == 'pending') {
            $this->transactionRepo->update($orderId, ['status' => 'pending']);
        }

        if ($isSuccess && $transaction->status !== 'success') {
            $this->transactionRepo->update($orderId, ['status' => 'success']);

            Enrollment::firstOrCreate([
                'user_id' => $transaction->user_id,
                'course_id' => $transaction->course_id,
            ], [
                'enrolled_at' => now(),
                'status' => 'active',
                'progress_percentage' => 0,
            ]);

            Log::info('MIDTRANS SUCCESS: Payment confirmed for order ' . $orderId);
        }

        return ['status' => 'success', 'code' => 200, 'message' => 'Callback processed'];
    }

    /**
     * Generate unique invoice number
     * Format: INV-YYYYMMDD-XXX
     */
    private function generateInvoiceNumber(): string
    {
        $today = now()->format('Ymd');
        $count = Transaction::whereDate('created_at', today())->count() + 1;

        return 'INV-' . $today . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
