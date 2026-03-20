<?php

namespace App\Services;

use App\Repositories\ReviewRepository;
use App\Repositories\EnrollmentRepository; // للتحقق من التسجيل

class ReviewService
{
    protected $reviewRepo;
    protected $enrollmentRepo;

    public function __construct(ReviewRepository $reviewRepo, EnrollmentRepository $enrollmentRepo)
    {
        $this->reviewRepo = $reviewRepo;
        $this->enrollmentRepo = $enrollmentRepo;
    }

    public function createReview($user, $courseId, array $data)
    {
        // 1. التحقق: هل المستخدم مسجل في الدورة؟
        // (يمكنك إضافة شرط: هل أكمل الدورة؟ سنكتفي بالتسجيل الآن)
        
        // نحتاج للتحقق من وجود تسجيل في أي دفعة تابعة لهذه الدورة
        $isEnrolled = $user->cohorts()->whereHas('course', fn($q) => $q->where('id', $courseId))->exists();

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