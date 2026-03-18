<?php

namespace App\Services;

use App\Repositories\CohortRepository;
use App\Repositories\CourseRepository; // نحتاجه للتحقق من الملكية
use App\Models\User;

class CohortService
{
    protected $cohortRepo;
    protected $courseRepo;

    public function __construct(CohortRepository $cohortRepo, CourseRepository $courseRepo)
    {
        $this->cohortRepo = $cohortRepo;
        $this->courseRepo = $courseRepo;
    }

    // --- منطق المدرب (Instructor) ---

    public function createCohort($user, array $data)
    {
        $course = $this->courseRepo->find($data['course_id'] ?? null);

        if (!$course) {
            return ['error' => 'Course not found.', 'code' => 404];
        }

        // قاعدة: فقط مالك الدورة يمكنه إنشاء دفعات
        if ($course->institution->user_id !== $user->id) {
            return ['error' => 'Unauthorized.', 'code' => 403];
        }

        $cohort = $this->cohortRepo->create($data);

        return ['success' => true, 'data' => $cohort];
    }

    public function updateCohort($user, $cohortId, array $data)
    {
        $cohort = $this->cohortRepo->find($cohortId);

        if (!$cohort) {
            return ['error' => 'Cohort not found.', 'code' => 404];
        }

        if ($cohort->course->institution->user_id !== $user->id) {
            return ['error' => 'Unauthorized.', 'code' => 403];
        }

        $this->cohortRepo->update($cohort, $data);

        return ['success' => true, 'data' => $cohort];
    }

    // --- منطق الطالب (Student) ---

    public function enrollStudent(User $user, $cohortId)
    {
        $cohort = $this->cohortRepo->find($cohortId);

        if (!$cohort) {
            return ['error' => 'Cohort not found.', 'code' => 404];
        }

        // 1. هل الدورة منشورة؟
        if ($cohort->course->status !== 'published') {
            return ['error' => 'This course is not available for enrollment.', 'code' => 403];
        }

        // 2. هل مسجل مسبقاً؟
        if ($this->cohortRepo->isUserEnrolled($user->id, $cohort->id)) {
            return ['error' => 'You are already enrolled.', 'code' => 409];
        }

        // 3. هل المقاعد ممتلئة؟
        $currentCount = $this->cohortRepo->getStudentsCount($cohort->id);
        if ($currentCount >= $cohort->max_students) {
            return ['error' => 'This cohort is full.', 'code' => 400];
        }

        // 4. تنفيذ التسجيل
        $this->cohortRepo->enrollUser($cohort, $user);

        // يمكن هنا إرسال إيميل ترحيبي (Logic for notifications)

        return ['success' => true, 'message' => 'Enrollment successful.'];
    }
    
    // --- منطق مشترك ---
    
    public function getCohortStudents($user, $cohortId)
    {
        $cohort = $this->cohortRepo->find($cohortId);
        
        if (!$cohort) {
            return ['error' => 'Not found', 'code' => 404];
        }
        
        // قاعدة: فقط المدرب المالك يمكنه رؤية الطلاب
        if ($cohort->course->institution->user_id !== $user->id) {
             return ['error' => 'Unauthorized.', 'code' => 403];
        }
        
        $students = $this->cohortRepo->getCohortStudents($cohortId);
        
        return ['success' => true, 'data' => $students];
    }
}