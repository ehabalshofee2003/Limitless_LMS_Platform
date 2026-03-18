<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cohort extends Model
{
    protected $fillable = [
        'course_id', 'name', 'start_date', 'end_date', 'max_students', 'google_meet_link'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // الدورة الأم
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // الطلاب المسجلين في هذه الدفعة
    public function students()
    {
        return $this->belongsToMany(User::class, 'cohort_user', 'cohort_id', 'user_id')
                    ->withPivot([
                        'progress_percentage', 
                        'attendance_percentage', 
                        'final_exam_grade', 
                        'instructor_rating', 
                        'certificate_issued'
                    ])
                    ->withTimestamps();
    }
}