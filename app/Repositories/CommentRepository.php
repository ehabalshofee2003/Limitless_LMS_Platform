<?php

namespace App\Repositories;

use App\Models\Comment;

class CommentRepository
{
    public function create(array $data)
    {
        return Comment::create($data);
    }

    public function getRootComments($commentableId, $commentableType)
    {
        // نجلب فقط التعليقات الرئيسية (التي ليس لها أب) ونحمل معها الردود
        return Comment::where('commentable_id', $commentableId)
            ->where('commentable_type', $commentableType)
            ->whereNull('parent_id') // جلب الجذور فقط
            ->with('repliesRecursive') // تحميل الشجرة كاملة
            ->latest()
            ->get();
    }
      public function find($id)
    {
        return Comment::find($id);
    }
}