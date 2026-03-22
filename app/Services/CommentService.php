<?php

namespace App\Services;

use App\Repositories\CommentRepository;
use App\Notifications\ReplyToCommentNotification; // سننشئه لاحقاً
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

        // إرسال إشعار لصاحب التعليق الأصلي (إذا كان رداً)
        if ($parentId) {
            $parentComment = $this->commentRepo->find($parentId);
            if ($parentComment && $parentComment->user_id !== $user->id) {
                $parentComment->user->notify(new ReplyToCommentNotification($comment));
            }
        }
    preg_match_all('/@([a-zA-Z0-9_]+)/', $body, $matches);
        $usernames = $matches[1] ?? [];

        if ($usernames) {
            $mentionedUsers = User::whereIn('name', $usernames)->get();
            foreach ($mentionedUsers as $mentionedUser) {
                // إرسال إشعار "تم ذكرك في تعليق"
                $mentionedUser->notify(new MentionedInCommentNotification($comment));
            }
        }

 
        return $comment;
    }
    
    // ... دالة جلب التعليقات
}