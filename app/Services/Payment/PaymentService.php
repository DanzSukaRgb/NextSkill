<?php

namespace App\Services\Payment;

use App\Models\Course;
use App\Models\User;
use App\Models\Enrollment;
use App\Repositories\Payment\TransactionRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
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

        $transaction = $this->transactionRepo->create([
            'id' => $transactionId,
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

        $snapToken = Snap::getSnapToken($params);
        $paymentUrl = \Midtrans\Snap::createTransaction($params)->redirect_url;
        
        $this->transactionRepo->update($transactionId, [
            'snap_token' => $snapToken,
            'payment_url' => $paymentUrl,
        ]);

        return [
            'status' => 'success',
            'snap_token' => $snapToken,
            'payment_url' => $paymentUrl,
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

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';

        $transaction = $this->transactionRepo->findById($orderId);
        if (!$transaction) {
            return ['status' => 'error', 'code' => 404, 'message' => 'Transaction not found'];
        }

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
        }

        return ['status' => 'success', 'code' => 200, 'message' => 'Callback processed'];
    }
}
