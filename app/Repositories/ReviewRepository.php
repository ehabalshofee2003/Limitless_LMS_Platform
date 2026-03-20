<?php

namespace App\Repositories;

use App\Models\Review;

class ReviewRepository
{
    public function create(array $data)
    {
        return Review::create($data);
    }

    public function findByUserAndCourse($userId, $courseId)
    {
        return Review::where('user_id', $userId)
                     ->where('course_id', $courseId)
                     ->first();
    }

    public function getCourseReviews($courseId)
    {
        return Review::where('course_id', $courseId)
                     ->with('user:id,name') // جلب اسم المقيم فقط
                     ->latest()
                     ->get();
    }
}