<?php

namespace App\Listeners;

use Illuminate\Notifications\Events\NotificationFailed;
use App\Models\FcmToken;

class DeleteInvalidFcmToken
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NotificationFailed $event): void
    {
        // 1. التحقق من أن القناة هي Firebase
        // نتحقق مما إذا كان اسم القناة يحتوي على كلمة Firebase
        if (str_contains($event->channel, 'Firebase')) {
            
            // 2. محاولة استخراج التوكن من البيانات المرجعة
            // هيكل البيانات قد يختلف قليلاً، نتحقق من وجود التوكن
            $token = $event->data['token'] ?? null;

            if ($token) {
                // 3. حذف التوكن من قاعدة البيانات
                FcmToken::where('token', $token)->delete();
            }
        }
    }
}