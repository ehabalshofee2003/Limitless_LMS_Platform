<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; // <--- مهم جداً
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles; // <--- أضف HasRoles

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // --- العلاقات ---

    // 1. علاقة: المستخدم قد يكون "مالك لمؤسسة"
    public function institution()
    {
        return $this->hasOne(Institution::class);
    }

    // 2. علاقة: الدفعات التي سجل فيها الطالب (Many-to-Many)
    public function cohorts()
    {
        return $this->belongsToMany(Cohort::class, 'cohort_user', 'user_id', 'cohort_id')
                    ->withPivot([
                        'progress_percentage', 
                        'attendance_percentage', 
                        'total_quiz_grade', 
                        'final_exam_grade', 
                        'instructor_rating', 
                        'certificate_issued'
                    ])
                    ->withTimestamps();
    }

    // 3. علاقة: تقدم الطالب في الدروس (Many-to-Many)
    public function lessonProgress()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_user', 'user_id', 'lesson_id')
                    ->withPivot('is_completed', 'watch_seconds')
                    ->withTimestamps();
    }

    // app/Models/User.php


public function sendPasswordResetNotification($token)
{
    // هنا نحدد رابط الـ Frontend
    // مثال: https://my-frontend-app.com/reset-password?token=...
    $url = 'https://my-frontend-app.com/reset-password?token=' . $token . '&email=' . $this->email;

    $this->notify(new ResetPasswordNotification($url));
}
}