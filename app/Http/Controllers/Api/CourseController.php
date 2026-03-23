<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CourseService;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Resources\CourseResource;
use App\Http\Requests\UpdateCourseRequest;
use Illuminate\Support\Facades\Cache; // <--- 1. استدعاء Cache

class CourseController extends Controller
{
    protected $courseService;

    public function show($id)
    {
        // 3. تخزين تفاصيل دورة واحدة
        // لاحظ استخدام المفتاح الديناميكي 'course.' . $id
        $course = Cache::remember("course.{$id}", 3600, function () use ($id) {
            return Course::with('institution', 'cohorts')->find($id);
        });

        if (!$course || $course->status !== 'published') {
            return response()->json(['message' => 'Course not found'], 404);
        }

        return response()->json($course);
    }
    

 public function index(Request $request)
{
    // 1. نبدأ الاستعلام مع الـ Caching
    // ملاحظة: مع الفلترة الديناميكية، الـ Cache يصبح معقداً قليلاً.
    // الحل الاحترافي هو استخدام "Cache Key" فريد لكل组合 من الفلاتر.
    
    $cacheKey = 'courses.' . md5(json_encode($request->all()));

    $courses = Cache::remember($cacheKey, 60, function () use ($request) {
        
        // 2. تطبيق الفلترة
        // نمرر كل الباراميترات القادمة من الـ Request ($request->all())
        return Course::query()
            ->where('status', 'published') // نجلب المنشورة فقط للجمهور
            ->filter($request->all())      // استدعاء الـ Scope الذي كتبناه
            ->with('institution')          // جلب اسم المؤسسة
            ->paginate(10);                // التقسيم (10 دورات في الصفحة)
    });

    // 3. إرجاع النتيجة
    // استخدام API Resource لتنسيق الرد
    return CourseResource::collection($courses);
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