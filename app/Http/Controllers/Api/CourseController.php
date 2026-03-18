<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CourseService;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;

class CourseController extends Controller
{
    protected $courseService;
public function show($id){
    $course = Course::findOrFail($id);

    return response()->json([
        'status' => 'success',
        'data' => $course
    ]);}
public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    // عرض الدورات (عام)
public function index()
    {
        // نستخدم Repository مباشرة للقراءة البسيطة أو عبر Service
        // هنا سنستخدم Service للتبسيط، أو يمكن حقن Repository في Controller للقراءة
        // لكن للحفاظ على البساطة سنستخدم Service
        // ملاحظة: يمكن إنشاء دالة index في Service إذا كانت تحتاج فلترة معقدة
        
        $courses = Course::where('status', 'published')->get(); 
        // أو يمكن نقل هذا السطر لدالة في CourseRepository وحقنها هنا.
        // للتبسيط الآن: قراءة البيانات البسيطة يمكن عملها مباشرة أو عبر دالة في Service.
        
        // الطريقة الاحترافية الكاملة:
        // $courses = $this->courseService->getAllPublished(); 
        
        return response()->json($courses);
    }

    // إنشاء دورة
public function store(StoreCourseRequest $request)
    {
        $result = $this->courseService->createCourse($request->user(), $request->validated());

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json([
            'message' => 'Course created as draft.',
            'data' => $result['data']
        ], 201);
    }
    // تحديث دورة
public function update(UpdateCourseRequest $request, $id)
    {
        $result = $this->courseService->updateCourse($request->user(), $id, $request->validated());

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json($result['data']);
    }

    // طلب النشر
    public function publish(Request $request, $id)
    {
        $result = $this->courseService->publishRequest($request->user(), $id);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }

        return response()->json(['message' => $result['message']]);
    }
    
    // حذف
    public function destroy(Request $request, $id)
    {
        $result = $this->courseService->deleteCourse($request->user(), $id);
        
        if (isset($result['error'])) {
            return response()->json(['message' => $result['error']], $result['code']);
        }
        
        return response()->json(['message' => $result['message']]);
    }
}