<?php

namespace App\Services;

use App\Repositories\PaymentRepository;
use App\Repositories\CohortRepository;
use App\Models\User;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PaymentService
{
    protected $paymentRepo;
    protected $cohortRepo;

    public function __construct(PaymentRepository $paymentRepo, CohortRepository $cohortRepo)
    {
        $this->paymentRepo = $paymentRepo;
        $this->cohortRepo = $cohortRepo;
        
        // إعداد مفاتيح Stripe
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * إنشاء جلسة دفع
     */
    public function createCheckoutSession(User $user, $cohortId)
    {
        $cohort = $this->cohortRepo->find($cohortId);

        if (!$cohort) {
            return ['error' => 'Cohort not found.', 'code' => 404];
        }

        // منطق العمل: هل هو مسجل مسبقاً؟
        if ($this->cohortRepo->isUserEnrolled($user->id, $cohort->id)) {
            return ['error' => 'Already enrolled.', 'code' => 409];
        }

        // منطق العمل: هل الدورة مجانية؟
        if ($cohort->course->price <= 0) {
            // يمكن هنا استدعاء CohortService->enrollStudent مباشرة
            return ['error' => 'Free course, no payment needed.', 'code' => 400];
        }

        // 1. إنشاء سجل دفع "معلق" في قاعدتنا
        $payment = $this->paymentRepo->create([
            'user_id' => $user->id,
            'cohort_id' => $cohort->id,
            'amount' => $cohort->course->price,
            'status' => 'pending',
            'transaction_id' => 'temp_' . uniqid(),
        ]);

        // 2. إنشاء جلسة Stripe
        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => $cohort->course->title],
                    'unit_amount' => $cohort->course->price * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => config('app.frontend_url') . '/success',
            'cancel_url' => config('app.frontend_url') . '/cancel',
            'metadata' => [
                'payment_id' => $payment->id, // مهم جداً للربط لاحقاً
            ]
        ]);

        // تحديث السجل برابط الجلسة (اختياري)
        $this->paymentRepo->update($payment, ['transaction_id' => $checkoutSession->id]);

        return ['success' => true, 'url' => $checkoutSession->url];
    }

    /**
     * معالجة نجاح الدفع (من Webhook)
     */
    public function handleSuccess($paymentId, $stripeTransactionId)
    {
        $payment = $this->paymentRepo->find($paymentId);

        if (!$payment || $payment->status === 'completed') {
            return false; // تمت معالجته سابقاً أو غير موجود
        }

        // 1. تحديث حالة الدفع
        $this->paymentRepo->update($payment, [
            'status' => 'completed',
            'transaction_id' => $stripeTransactionId
        ]);

        // 2. تسجيل الطالب في الدفعة (استخدام CohortRepository)
        $cohort = $this->cohortRepo->find($payment->cohort_id);
        $user = User::find($payment->user_id);

        if ($cohort && $user) {
            $this->cohortRepo->enrollUser($cohort, $user);
        }

        return true;
    }
}