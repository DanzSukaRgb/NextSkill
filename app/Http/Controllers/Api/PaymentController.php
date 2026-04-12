<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\CheckoutRequest;
use App\Models\Course;
use App\Services\Payment\PaymentService;
use App\Helpers\BaseResponse;

class PaymentController extends Controller
{
    private $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function checkout(CheckoutRequest $request)
    {
        $course = Course::findOrFail($request->course_id);
        $user = auth()->user();

        if ($course->price <= 0) {
            return BaseResponse::Error('This course is free, use enrollment endpoint instead.');
        }

        try {
            $result = $this->paymentService->checkout($course, $user);
            return BaseResponse::Success('Transaction initialized successfully', $result);
        } catch (\Exception $e) {
            return BaseResponse::Error($e->getMessage());
        }
    }
}
