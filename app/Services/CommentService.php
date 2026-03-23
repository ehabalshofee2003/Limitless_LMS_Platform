<?php

namespace App\Services;

use App\Repositories\CommentRepository;
use App\Models\User; // <--- تصحيح الاستيراد
use App\Models\Comment;
use App\Notifications\ReplyToCommentNotification; // <--- تصحيح الاستيراد
use App\Notifications\MentionedInCommentNotification; // <--- تصحيح الاستيراد
use Illuminate\Support\Facades\Notification;

class CommentService
{
    protected $commentRepo;

    public function __construct(CommentRepository $commentRepo)
    {
        $this->commentRepo = $commentRepo;
    }

    public function addComment($user, $commentable, $body, $parentId = null)
    {
        $data = [
            'user_id' => $user->id,
            'commentable_id' => $commentable->id,
            'commentable_type' => get_class($commentable),
            'body' => $body,
            'parent_id' => $parentId,
        ];

        $comment = $this->commentRepo->create($data);

        // 1. إشعار الرد
        if ($parentId) {
            $parentComment = $this->commentRepo->find($parentId);
            // لا ترسل إشعار لنفسك
            if ($parentComment && $parentComment->user_id !== $user->id) {
                $parentComment->user->notify(new ReplyToCommentNotification($comment));
            }
        }

        // 2. إشعار المنشن (@username)
        preg_match_all('/@([a-zA-Z0-9_]+)/', $body, $matches);
        $usernames = $matches[1] ?? [];

        if ($usernames) {
            // البحث عن المستخدمين الذين تم ذكرهم
            $mentionedUsers = User::whereIn('name', $usernames)->get();
            
            foreach ($mentionedUsers as $mentionedUser) {
                // لا ترسل إشعار لنفسك
                if ($mentionedUser->id !== $user->id) {
                     $mentionedUser->notify(new MentionedInCommentNotification($comment));
                }
            }
        }

        return $comment;
    }
    
    public function getComments($commentableId, $commentableType)
    {
        return Comment::where('commentable_id', $commentableId)
            ->where('commentable_type', $commentableType)
            ->whereNull('parent_id') // جلب التعليقات الرئيسية فقط
            ->with('user:id,name') 
            ->latest()
            ->get();
    }
}