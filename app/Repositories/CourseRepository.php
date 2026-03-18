<?php

namespace App\Repositories;

use App\Models\Course;

class CourseRepository
{
    // جلب الدورات المنشورة للجميع
    public function getPublished()
    {
        return Course::where('status', 'published')->latest()->get();
    }

    // إيجاد دورة بالمعرف
    public function find($id)
    {
        return Course::find($id);
    }

    // إنشاء دورة جديدة
    public function create(array $data)
    {
        return Course::create($data);
    }

    // تحديث دورة
    public function update(Course $course, array $data)
    {
        return $course->update($data);
    }

    // حذف دورة
    public function delete(Course $course)
    {
        return $course->delete();
    }
}