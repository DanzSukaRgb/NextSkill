<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\PaymentCallbackRequest;
use App\Services\Payment\PaymentService;
use App\Helpers\BaseResponse;

class PaymentCallbackController extends Controller
{
    private $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function callback(PaymentCallbackRequest $request)
    {
        // Get all payload since Midtrans sens dynamic fields
        $payload = $request->all();
        $result = $this->paymentService->handleCallback($payload);

        if (isset($result['status']) && $result['status'] === 'error') {
            return BaseResponse::Error($result['message']);
        }

        return BaseResponse::Success($result['message'], null);
    }
}
