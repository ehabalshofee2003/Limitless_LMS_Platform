<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LessonService;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLessonRequest;

class LessonController extends Controller
{
    protected $lessonService;

    public function __construct(LessonService $lessonService)
    {
        $this->lessonService = $lessonService;
    }
 
    // إضافة درس (مدرب)

    public function store(StoreLessonRequest $request)
    {
        $result = $this->lessonService->createLesson($request->user(), $request->validated());

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json($result['data'], 201);
    }

    // تحديث درس (مدرب)
 
    public function update(StoreLessonRequest $request, $id)
    {
        $result = $this->lessonService->updateLesson($request->user(), $id, $request->validated());

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json($result['data']);
    }

    // إكمال درس (طالب)
    public function complete(Request $request, $id)
    {
        $result = $this->lessonService->markAsCompleted($request->user(), $id);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json([
            'message' => $result['message'],
            'progress' => $result['new_progress']
        ]);
    }
    
    // عرض دروس دفعة (طالب)
    public function byCohort(Request $request, $cohortId)
    {
        $result = $this->lessonService->getStudentLessons($request->user(), $cohortId);
        
        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }
        
        return response()->json($result['data']);
    }
        public function uploadResource(Request $request)
    {
        // 1. التحقق من وجود ملف
        $request->validate([
            'file' => 'required|file|max:102400', // حد أقصى 100MB (تأكد من إعدادات PHP)
        ]);

        $file = $request->file('file');

        // 2. استدعاء الخدمة
        $data = $this->lessonService->uploadResource($file);

        // 3. الرد
        return response()->json([
            'message' => 'File uploaded successfully.',
            'data' => $data
        ], 201);
    }
}