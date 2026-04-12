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
            $result = $this->paymentService->enrollFreeCourse($course, $user);
            
            if ($result['status'] === 'error') {
                return BaseResponse::Error($result['message'], $result['code']);
            }

            return BaseResponse::Success($result['message'], null);
        }

        try {
            $result = $this->paymentService->checkout($course, $user);
            return BaseResponse::Success('Transaction initialized successfully', $result);
        } catch (\Exception $e) {
            return BaseResponse::Error($e->getMessage());
        }
    }
}
