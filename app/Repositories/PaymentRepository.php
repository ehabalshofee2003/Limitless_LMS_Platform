<?php

namespace App\Repositories;

use App\Models\Payment;

class PaymentRepository
{
    // إنشاء سجل دفع مبدئي
    public function create(array $data)
    {
        return Payment::create($data);
    }

    // تحديث حالة الدفع
    public function update(Payment $payment, array $data)
    {
        return $payment->update($data);
    }

    // إيجاد دفع بواسطة المعرف
    public function find($id)
    {
        return Payment::find($id);
    }

    // جلب سجل الدفع باستخدام Transaction ID من Stripe
    public function findByTransactionId($transactionId)
    {
        return Payment::where('transaction_id', $transactionId)->first();
    }
    
    // جلب تاريخ المدفوعات للمستخدم
    public function getUserPayments($userId)
    {
        return Payment::where('user_id', $userId)->latest()->get();
    }
}