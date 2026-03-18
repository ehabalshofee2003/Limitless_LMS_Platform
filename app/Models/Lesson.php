<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
  protected $fillable = [
        'course_id', 'title', 'description', 'type', 'resource_path', 'order', 'duration_minutes'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'lesson_user')->withPivot('is_completed', 'watch_seconds');
    }

    //  من شاهد هذا الدرس (للتقارير)
    public function viewers()
    {
        return $this->belongsToMany(User::class, 'lesson_user', 'lesson_id', 'user_id')
                    ->withPivot('is_completed', 'watch_seconds')
                    ->withTimestamps();
    }
}