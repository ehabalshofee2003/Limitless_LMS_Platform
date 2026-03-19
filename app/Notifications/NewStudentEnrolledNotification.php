<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewStudentEnrolledNotification extends Notification
{
    use Queueable;

    protected $studentName;
    protected $courseName;

    public function __construct($studentName, $courseName)
    {
        $this->studentName = $studentName;
        $this->courseName = $courseName;
    }

    public function via($notifiable): array
    {
        return ['database']; // يكفي إشعار داخلي هنا
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => '🆕 New Student Enrollment',
            'message' => "Student '{$this->studentName}' has enrolled in your course: {$this->courseName}",
            'icon' => '👤',
        ];
    }
}