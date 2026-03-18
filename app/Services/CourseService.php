<?php

namespace App\Services;

use App\Repositories\CourseRepository;
use Illuminate\Support\Str;

class CourseService
{
    protected $courseRepo;

    public function __construct(CourseRepository $courseRepo)
    {
        $this->courseRepo = $courseRepo;
    }

    // إنشاء دورة جديدة
    public function createCourse($user, array $data)
    {
        // منطق العمل: يجب أن يكون لدى المستخدم ملف مؤسسة أولاً
        if (!$user->institution) {
            return ['error' => 'You must create an institution profile first.', 'code' => 403];
        }

        // تجهيز البيانات
        $data['institution_id'] = $user->institution->id;
        $data['slug'] = Str::slug($data['title']);
        $data['status'] = 'draft'; // الدورات تبدأ كمسودات

        $course = $this->courseRepo->create($data);

        return ['success' => true, 'data' => $course];
    }

    // تحديث دورة
    public function updateCourse($user, $courseId, array $data)
    {
        $course = $this->courseRepo->find($courseId);

        if (!$course) {
            return ['error' => 'Course not found.', 'code' => 404];
        }

        // منطق العمل: هل المستخدم هو مالك المؤسسة التي تملك الدورة؟
        if ($course->institution->user_id !== $user->id) {
            return ['error' => 'Unauthorized.', 'code' => 403];
        }

        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $this->courseRepo->update($course, $data);

        return ['success' => true, 'data' => $course];
    }

    // طلب النشر (إرسال للمشرف)
    public function publishRequest($user, $courseId)
    {
        $course = $this->courseRepo->find($courseId);

        if (!$course) {
            return ['error' => 'Course not found.', 'code' => 404];
        }

        // التحقق من الملكية
        if ($course->institution->user_id !== $user->id) {
            return ['error' => 'Unauthorized.', 'code' => 403];
        }

        // منطق العمل: لا يمكن نشر دورة بدون دروس
        if ($course->lessons->isEmpty()) {
            return ['error' => 'Cannot publish an empty course.', 'code' => 400];
        }

        // تغيير الحالة إلى "قيد الانتظار"
        $this->courseRepo->update($course, ['status' => 'pending']);

        return ['success' => true, 'message' => 'Course submitted for approval.'];
    }
    
    // حذف دورة
    public function deleteCourse($user, $courseId)
    {
        $course = $this->courseRepo->find($courseId);
        
        if (!$course) {
            return ['error' => 'Course not found.', 'code' => 404];
        }
        
        if ($course->institution->user_id !== $user->id) {
            return ['error' => 'Unauthorized.', 'code' => 403];
        }
        
        $this->courseRepo->delete($course);
        
        return ['success' => true, 'message' => 'Course deleted.'];
    }
}