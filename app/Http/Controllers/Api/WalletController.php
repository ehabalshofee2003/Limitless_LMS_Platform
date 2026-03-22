<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\Transaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    // 1. عرض رصيد المحفظة
    public function balance(Request $request)
    {
        $wallet = $request->user()->wallet()->firstOrCreate([]);
        return response()->json([
            'available_balance' => $wallet->balance,
            'pending_balance' => $wallet->pending_balance,
        ]);
    }

    // 2. عرض سجل المعاملات
    public function transactions(Request $request)
    {
        $transactions = $request->user()->transactions()->latest()->paginate(15);
        return response()->json($transactions);
    }

    // 3. طلب سحب الأموال (للمدرب)
    public function requestPayout(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'payment_method' => 'required|string',
            'details' => 'required|string', // تفاصيل الحساب البنكي
        ]);
        
        $user = $request->user();
        $wallet = $user->wallet()->firstOrCreate([]);

        // التحقق من الرصيد المتاح
        if ($wallet->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient available balance.'], 400);
        }

        // إنشاء طلب السحب
        $payout = Payout::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'status' => 'pending',
            'payment_method' => $request->payment_method,
            'details' => $request->details,
        ]);

        // خصم المبلغ من الرصيد وتسجيله كمعاملة
        $wallet->balance -= $request->amount;
        $wallet->save();

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'payout',
            'amount' => $request->amount,
            'balance_after' => $wallet->balance,
            'description' => 'Payout request #' . $payout->id,
            'transactionable_id' => $payout->id,
            'transactionable_type' => Payout::class,
        ]);

        return response()->json([
            'message' => 'Payout request submitted successfully.',
            'data' => $payout
        ]);
    }
    
    // 4. سجل طلبات السحب
    public function payoutHistory(Request $request)
    {
        $payouts = $request->user()->payouts()->latest()->get();
        return response()->json($payouts);
    }
}