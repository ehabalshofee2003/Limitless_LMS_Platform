<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CommentService;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    protected $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    // إضافة تعليق
    public function store(Request $request)
    {
        $request->validate([
            'commentable_id' => 'required|integer',
            'commentable_type' => 'required|string|in:App\Models\Course,App\Models\Lesson',
            'body' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        // جلب الـ Model (Course أو Lesson)
        $modelClass = $request->commentable_type;
        $model = $modelClass::find($request->commentable_id);

        if (!$model) {
            return response()->json(['message' => 'Resource not found.'], 404);
        }

        $comment = $this->commentService->addComment(
            $request->user(),
            $model,
            $request->body,
            $request->parent_id
        );

        return response()->json([
            'message' => 'Comment added successfully.',
            'data' => $comment
        ], 201);
    }

    // جلب تعليقات الدرس
    public function indexByLesson($lessonId)
    {
        $lesson = Lesson::find($lessonId);
        if (!$lesson) return response()->json(['message' => 'Lesson not found'], 404);
        
        $comments = $this->commentService->getComments($lessonId, Lesson::class);
        return response()->json($comments);
    }

    // جلب تعليقات الدورة
    public function indexByCourse($courseId)
    {
        $course = Course::find($courseId);
        if (!$course) return response()->json(['message' => 'Course not found'], 404);

        $comments = $this->commentService->getComments($courseId, Course::class);
        return response()->json($comments);
    }
}