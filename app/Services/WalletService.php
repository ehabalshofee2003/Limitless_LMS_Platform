<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * إيداع أموال (للطالب عند الدفع أو للمدرب عند الربح)
     */
    public function deposit(User $user, $amount, $description, $relatedModel = null)
    {
        return DB::transaction(function () use ($user, $amount, $description, $relatedModel) {
            $wallet = $user->wallet()->firstOrCreate([]);
            
            $wallet->balance += $amount;
            $wallet->save();

            return Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'description' => $description,
                'transactionable_id' => $relatedModel?->id,
                'transactionable_type' => $relatedModel ? get_class($relatedModel) : null,
            ]);
        });
    }

    /**
     * خصم أموال (للطالب عند الشراء)
     */
    public function withdraw(User $user, $amount, $description, $relatedModel = null)
    {
        return DB::transaction(function () use ($user, $amount, $description, $relatedModel) {
            $wallet = $user->wallet()->firstOrCreate([]);

            if ($wallet->balance < $amount) {
                throw new \Exception('Insufficient balance.');
            }

            $wallet->balance -= $amount;
            $wallet->save();

            return Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'description' => $description,
                'transactionable_id' => $relatedModel?->id,
                'transactionable_type' => $relatedModel ? get_class($relatedModel) : null,
            ]);
        });
    }

    /**
     * عملية معقدة: شراء دورة
     * 1. خصم من الطالب.
     * 2. إضافة للمدرب (بعد خصم عمولة المنصة).
     */
    public function purchaseCourse(User $student, User $instructor, $course, $platformFeePercent = 20)
    {
        return DB::transaction(function () use ($student, $instructor, $course, $platformFeePercent) {
            $totalPrice = $course->price;

            // 1. خصم من الطالب
            $this->withdraw($student, $totalPrice, "Purchase of course: " . $course->title, $course);

            // 2. حساب حصة المدرب
            $instructorShare = $totalPrice * ( (100 - $platformFeePercent) / 100 );
            $platformShare = $totalPrice - $instructorShare;

            // 3. إيداع للمدرب (في الرصيد المعلق Pending)
            // نفترض أن لدينا حقل pending_balance في جدول الـ Wallet
            $instructorWallet = $instructor->wallet()->firstOrCreate([]);
            $instructorWallet->pending_balance += $instructorShare;
            $instructorWallet->save();

            Transaction::create([
                'user_id' => $instructor->id,
                'type' => 'earning',
                'amount' => $instructorShare,
                'balance_after' => $instructorWallet->balance, // لا يزال في المعلق
                'description' => "Earning from course: " . $course->title,
                'transactionable_id' => $course->id,
                'transactionable_type' => get_class($course),
            ]);

            return true;
        });
    }
}