<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'institution_id', 'title', 'slug', 'description', 'price', 'status', 'approved_by'
    ];

    // المؤسسة المالكة للدورة
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    // الدفعات (Cohorts) التابعة لهذه الدورة
    public function cohorts()
    {
        return $this->hasMany(Cohort::class);
    }

    // الدروس التابعة للدورة
    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }
}