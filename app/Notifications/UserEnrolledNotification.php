<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Channels\FirebaseChannel;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

class UserEnrolledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $cohort;

    public function __construct($cohort)
    {
        $this->cohort = $cohort;
    }

    // تحديد القنوات: Database + Firebase
    public function via($notifiable)
    {
        return ['database', FirebaseChannel::class];
    }

    // بيانات قاعدة البيانات (كما كانت)
    public function toArray($notifiable)
    {
        return [
            'title' => 'New Enrollment',
            'message' => "You have been successfully enrolled in cohort: " . $this->cohort->name,
            'cohort_id' => $this->cohort->id,
        ];
    }

    // (جديد) بيانات Firebase
    public function toFirebase($notifiable)
    {
        return CloudMessage::new()
            ->withNotification(FcmNotification::create('Enrollment Confirmed', "You joined " . $this->cohort->name))
            ->withData([
                'cohort_id' => (string) $this->cohort->id,
                'type' => 'enrollment',
                'click_action' => 'OPEN_COURSE' // للأندرويد
            ]);
    }
}