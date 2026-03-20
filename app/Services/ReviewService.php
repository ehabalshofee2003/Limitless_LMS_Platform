<?php

namespace App\Services;

use App\Repositories\ReviewRepository;

class ReviewService
{
    protected $reviewRepo;

    // أزلنا EnrollmentRepository من هنا
    public function __construct(ReviewRepository $reviewRepo)
    {
        $this->reviewRepo = $reviewRepo;
    }

    public function createReview($user, $courseId, array $data)
    {
        // 1. التحقق: هل المستخدم مسجل في الدورة؟
        // نستخدم العلاقة الموجودة في مودل User مباشرة (cohorts)
        // ونبحث داخل دفعاته عن دفعة تتبع لهذه الدورة
        $isEnrolled = $user->cohorts()
                           ->whereHas('course', fn($q) => $q->where('id', $courseId))
                           ->exists();

        if (!$isEnrolled) {
            return ['error' => 'You must be enrolled in the course to review it.', 'code' => 403];
        }

        // 2. التحقق: هل قيم الدورة مسبقاً؟
        $existingReview = $this->reviewRepo->findByUserAndCourse($user->id, $courseId);
        if ($existingReview) {
            return ['error' => 'You have already reviewed this course.', 'code' => 409];
        }

        // 3. إنشاء التقييم
        $data['user_id'] = $user->id;
        $data['course_id'] = $courseId;
        
        $review = $this->reviewRepo->create($data);

        return ['success' => true, 'data' => $review];
    }

    public function getCourseReviews($courseId)
    {
        return $this->reviewRepo->getCourseReviews($courseId);
    }
}