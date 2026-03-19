<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail; // سننشئ هذا الملف في الخطوة التالية

class SendWelcomeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    // نستقبل بيانات المستخدم عند استدعاء المهمة
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    // الكود الذي سيتم تنفيذه في الخلفية
    public function handle(): void
    {
        // هنا نرسل الإيميل
        Mail::to($this->user->email)->send(new WelcomeEmail($this->user));
    }
}