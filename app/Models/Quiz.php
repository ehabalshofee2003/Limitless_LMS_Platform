<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'lesson_id', 'course_id', 'title', 'questions', 'passing_score'
    ];

    // هذا السطر هو الأهم، يحول النص إلى مصفوفة تلقائياً
    protected $casts = [
        'questions' => 'array', 
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}