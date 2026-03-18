<?php

namespace App\Services;

use App\Repositories\CohortRepository;
use App\Models\User;
use App\Models\Cohort;

class EnrollmentService
{
    protected $cohortRepo;

    // حقن الـ Repository عبر الـ Constructor (Dependency Injection)
    public function __construct(CohortRepository $cohortRepo)
    {
        $this->cohortRepo = $cohortRepo;
    }

    public function enroll(User $user, $cohortId)
    {
        // 1. جلب الدفعة
        $cohort = $this->cohortRepo->find($cohortId);

        if (!$cohort) {
            return ['error' => 'Cohort not found', 'code' => 404];
        }

        // 2. قواعد العمل: هل الدورة منشورة؟
        if ($cohort->course->status !== 'published') {
            return ['error' => 'Course not available', 'code' => 403];
        }

        // 3. قواعد العمل: هل مسجل مسبقاً؟ (نستخدم الـ Repository للسؤال)
        if ($this->cohortRepo->isUserEnrolled($user->id, $cohort->id)) {
            return ['error' => 'Already enrolled', 'code' => 409];
        }

        // 4. قواعد العمل: هل المقاعد ممتلئة؟
        $currentCount = $this->cohortRepo->getStudentsCount($cohort->id);
        if ($currentCount >= $cohort->max_students) {
            return ['error' => 'Cohort is full', 'code' => 400];
        }

        // 5. تنفيذ التسجيل (نستخدم الـ Repository للتنفيذ)
        $this->cohortRepo->enrollUser($cohort,$user);

        // 6. يمكن إضافة المزيد من المنطق هنا: إرسال إيميل، إشعار، إلخ
        // Mail::to($user)->send(...);

        return ['success' => true, 'data' => $cohort];
    }
}
