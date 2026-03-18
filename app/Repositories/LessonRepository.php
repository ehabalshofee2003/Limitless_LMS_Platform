<?php

namespace App\Repositories;

use App\Models\Lesson;
use App\Models\User;

class LessonRepository
{
    // إيجاد درس
    public function find($id)
    {
        return Lesson::find($id);
    }

    // إنشاء درس
    public function create(array $data)
    {
        return Lesson::create($data);
    }

    // تحديث درس
    public function update(Lesson $lesson, array $data)
    {
        return $lesson->update($data);
    }

    // حذف درس
    public function delete(Lesson $lesson)
    {
        return $lesson->delete();
    }

    // تسجيل إكمال الدرس في قاعدة البيانات (الجدول الوسيط)
    public function markAsCompleted(User $user, Lesson $lesson)
    {
        // استخدام updateOrCreate لتحديث السجل إذا كان موجوداً أو إنشائه
        return $user->lessonProgress()->updateOrCreate(
            ['lesson_id' => $lesson->id],
            ['is_completed' => true, 'watch_seconds' => $lesson->duration_minutes * 60]
        );
    }
    
    // جلب دروس دورة معينة
    public function getLessonsByCourse($courseId)
    {
        return Lesson::where('course_id', $courseId)->orderBy('order')->get();
    }
}