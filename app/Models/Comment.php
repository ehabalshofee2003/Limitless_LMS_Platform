<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['user_id', 'body', 'parent_id', 'commentable_id', 'commentable_type'];

    // صاحب التعليق
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // العنصر الذي تم التعليق عليه (درس، دورة)
    public function commentable()
    {
        return $this->morphTo();
    }

    // === العلاقات التشعبية (Recursive Relations) ===

    // 1. الردود (الأبناء)
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    // 2. الردود مع تحميل ردودها أيضاً (Recursive Eager Loading)
    // هذا يستخدم لجلب الشجرة كاملة بكود نظيف
    public function repliesRecursive()
    {
        return $this->replies()->with('repliesRecursive');
    }

    // 3. التعليق الأب (للصعود للأعلى)
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }
    
}