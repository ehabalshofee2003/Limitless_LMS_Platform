<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Stripe\Webhook;

class WebhookController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function handleStripe(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            
            // جلب الـ payment_id الذي أرسلناه سابقاً في metadata
            $paymentId = $session->metadata->payment_id;
            $transactionId = $session->payment_intent;

            // تفويض المنطق للخدمة
            $this->paymentService->handleSuccess($paymentId, $transactionId);
        }

        return response()->json(['status' => 'success']);
    }
}