<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// نطبق ShouldQueue لجعل الإرسال في الخلفية (أفضل للأداء)
class UserEnrolledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $cohort;

    // نستقبل بيانات الدفعة عند إنشاء الإشعار
    public function __construct($cohort)
    {
        $this->cohort = $cohort;
    }

    /**
     * تحديد قنوات الإرسال.
     * نستخدم ['database', 'mail'] ليعني:
     * 1. احفظ نسخة في قاعدة البيانات (ليشاهدها في الموقع).
     * 2. أرسل نسخة للإيميل.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * تمثيل الإشعار عندما يُقرأ من قاعدة البيانات (JSON).
     * هذا هو الشكل الذي سيراه الـ Frontend.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Enrollment',
            'message' => "You have been successfully enrolled in cohort: " . $this->cohort->name,
            'cohort_id' => $this->cohort->id,
            'icon' => '✅', // يمكن استخدام أيقونات أو روابط
        ];
    }

    /**
     * تمثيل الإشعار عندما يُرسل كإيميل.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Enrollment Confirmation')
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('You have been successfully enrolled in: ' . $this->cohort->name)
                    ->action('View Course', url('/courses/' . $this->cohort->course_id))
                    ->line('Thank you for using our application!');
    }
}