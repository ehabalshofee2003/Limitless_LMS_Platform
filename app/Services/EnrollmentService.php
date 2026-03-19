<?php

namespace App\Services;

use App\Repositories\CohortRepository;
use App\Models\User;
use App\Models\Cohort;
use App\Notifications\UserEnrolledNotification;
use App\Notifications\NewStudentEnrolledNotification;

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
        $user->notify(new UserEnrolledNotification($cohort));
       // 1. جلب مالك الدورة (Instructor)
        $instructor = $cohort->course->institution->user;
        
        // 2. إرسال الإشعار للمدرب
        if ($instructor) {
            $instructor->notify(new NewStudentEnrolledNotification(
                $user->name, // اسم الطالب
                $cohort->course->title // اسم الدورة
            ));
        }
        return ['success' => true, 'message' => 'Enrollment successful.'];

     }
        
    public function enrollStudent(User $user, $cohortId)
    {
        // ... المنطق القديم (التحقق والتسجيل) ...
        
        // بعد نجاح التسجيل:
        // $this->cohortRepo->enrollUser($cohort, $user);

        // ====> إرسال الإشعار <====
        // المتغير $user هو المستقبل، نستدعي notify ونعطيه كلاس الإشعار
       
    }
}
 