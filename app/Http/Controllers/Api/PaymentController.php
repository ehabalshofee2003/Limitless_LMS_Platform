<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Repositories\PaymentRepository; // لجلب السجل
use Illuminate\Http\Request;
use App\Http\Requests\CheckoutRequest;
class PaymentController extends Controller
{
    protected $paymentService;
    protected $paymentRepo;

    public function __construct(PaymentService $paymentService, PaymentRepository $paymentRepo)
    {
        $this->paymentService = $paymentService;
        $this->paymentRepo = $paymentRepo;
    }
 
    // بدء عملية الدفع

    public function checkout(CheckoutRequest $request)
    {
        $result = $this->paymentService->createCheckoutSession($request->user(), $request->validated()['cohort_id']);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json([
            'message' => 'Checkout session created.',
            'checkout_url' => $result['url']
        ]);
    }

    // سجل المدفوعات للطالب
    public function history(Request $request)
    {
        $payments = $this->paymentRepo->getUserPayments($request->user()->id);
        return response()->json($payments);
    }
}