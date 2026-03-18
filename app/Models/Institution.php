<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'is_verified', 'platform_commission', 'user_id'
    ];

    // المستخدم المالك للمؤسسة
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // الدورات التابعة للمؤسسة
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}