<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CommentService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    protected $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    // عرض التعليقات لدورة أو درس معين
    public function index(Request $request, $type, $id)
    {
        // تحديد المودل بناءً على النوع (lesson أو course)
        $modelClass = $type === 'lesson' ? \App\Models\Lesson::class : \App\Models\Course::class;
        
        // ملاحظة: هنا يجب التحقق من وجود المودل قبل جلب التعليقات
        
        $comments = $this->commentService->getComments($id, $modelClass);
        
        return response()->json($comments);
    }

    // إضافة تعليق أو رد
    public function store(Request $request)
    {
        $request->validate([
            'commentable_type' => 'required|string', // 'Lesson' or 'Course'
            'commentable_id' => 'required|integer',
            'body' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id', // إذا كان رداً
        ]);

        $user = $request->user();
        
        // حل بسيط لتحديد المودل (يمكن تحسينه)
        $modelClass = 'App\\Models\\' . $request->commentable_type;
        $model = app($modelClass)->find($request->commentable_id);

        if (!$model) {
            return response()->json(['message' => 'Target not found.'], 404);
        }

        $comment = $this->commentService->addComment(
            $user,
            $model,
            $request->body,
            $request->parent_id
        );

        return response()->json(['message' => 'Comment added.', 'data' => $comment], 201);
    }
}