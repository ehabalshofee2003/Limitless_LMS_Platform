<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase; // Facade الخاصة بالحزمة

class FirebaseChannel
{
    public function send($notifiable, Notification $notification)
    {
        // 1. جلب الرسالة المعدة للـ FCM
        $message = $notification->toFirebase($notifiable);

        // 2. جلب توكنات المستخدم (الأجهزة المسجلة)
        $tokens = $notifiable->fcmTokens()->pluck('token')->toArray();

        if (empty($tokens)) {
            return; // لا يوجد أجهزة مسجلة
        }

        try {
            // إرسال الإشعار
            Firebase::messaging()->sendMulticast($message, $tokens);
        } catch (\Exception $e) {
            // التعامل مع الأخطاء (مثلاً توكن منتهي الصلاحية)
            // \Log::error('FCM Error: ' . $e->getMessage());
        }
    }
}