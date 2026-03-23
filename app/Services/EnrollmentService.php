<?php

namespace App\Services;

use App\Repositories\CohortRepository;
use App\Repositories\CourseRepository;
use App\Services\DripContentService; // <--- استيراد الخدمة
use App\Notifications\UserEnrolledNotification;
use App\Notifications\NewStudentEnrolledNotification;
use App\Models\User;

class EnrollmentService
{
    protected $cohortRepo;
    protected $courseRepo;
    protected $dripService; // <--- تعريف الخاصية

    // حقن الخدمات في البناء
    public function __construct(
        CohortRepository $cohortRepo, 
        CourseRepository $courseRepo,
        DripContentService $dripService // <--- حقن الخدمة هنا
    ) {
        $this->cohortRepo = $cohortRepo;
        $this->courseRepo = $courseRepo;
        $this->dripService = $dripService;
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

        // 3. قواعد العمل: هل مسجل مسبقاً؟
        if ($this->cohortRepo->isUserEnrolled($user->id, $cohort->id)) {
            return ['error' => 'Already enrolled', 'code' => 409];
        }

        // 4. قواعد العمل: هل المقاعد ممتلئة؟
        $currentCount = $this->cohortRepo->getStudentsCount($cohort->id);
        if ($currentCount >= $cohort->max_students) {
            return ['error' => 'Cohort is full', 'code' => 400];
        }
   
        // 5. تنفيذ التسجيل
        $this->cohortRepo->enrollUser($cohort, $user);

        // 6. (جديد) فتح الدرس الأول تلقائياً باستخدام الخدمة المحقونة
        $this->dripService->unlockFirstLesson($user, $cohort);

        // 7. إرسال الإشعارات
        // إشعار للطالب
        $user->notify(new UserEnrolledNotification($cohort));
        
        // إشعار للمدرب
        $instructor = $cohort->course->institution->user;
        if ($instructor) {
            $instructor->notify(new NewStudentEnrolledNotification(
                $user->name,
                $cohort->course->title
            ));
        }

        return ['success' => true, 'message' => 'Enrollment successful.'];
    }
}