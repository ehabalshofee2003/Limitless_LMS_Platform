<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $courseName;
    protected $certificateUrl;

    public function __construct($courseName, $certificateUrl)
    {
        $this->courseName = $courseName;
        $this->certificateUrl = $certificateUrl;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail']; // نريد حفظها وإرسال إيميل
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => '🎉 Congratulations! Certificate Issued',
            'message' => "You have successfully obtained a certificate for the course: " . $this->courseName,
            'action_url' => $this->certificateUrl,
            'icon' => '🏆',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Your Certificate is Ready!')
                    ->greeting('Congratulations ' . $notifiable->name . '!')
                    ->line('You have successfully completed the course: ' . $this->courseName)
                    ->action('Download Certificate', $this->certificateUrl)
                    ->line('We wish you continued success!');
    }
}