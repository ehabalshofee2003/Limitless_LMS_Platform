<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Support\Facades\DB;

class CourseVersionService
{
   /**
 * Create new version of a course.
 *
 * @param Course $course
 * @return Course
 */
    public function createNewVersion(Course $originalCourse): Course
    {
        return DB::transaction(function () use ($originalCourse) {
            
            // 1. إنشاء سجل دورة جديد (نسخ البيانات الأساسية)
            $newCourse = $originalCourse->replicate();
            $newCourse->version = $originalCourse->version + 1;
            $newCourse->original_course_id = $originalCourse->original_course_id ?? $originalCourse->id;
            $newCourse->status = 'draft'; // النسخة تبدأ كمسودة
            $newCourse->push(); // حفظ النسخة الجديدة

            // 2. نسخ الدروس (Deep Copy)
            foreach ($originalCourse->lessons as $lesson) {
                $newLesson = $lesson->replicate();
                $newLesson->course_id = $newCourse->id;
                $newLesson->save();
            }

            return $newCourse;
        });
    }
    
        // داخل الكلاس
    public function createVersion($id, CourseVersionService $service)
    {
        $course = Course::findOrFail($id);

        // التحقق من الملكية
        if (auth()->id() !== $course->institution->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

            /** @var \App\Models\Course $newVersion */
        $newVersion = $service->createNewVersion($course);

        return response()->json([
            'message' => 'New version created successfully.',
            'new_course_id' => $newVersion->id,
            'version' => $newVersion->version
        ]);
    }
}