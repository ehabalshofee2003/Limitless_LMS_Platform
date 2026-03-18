<?php

namespace App\Services;

use App\Repositories\LessonRepository;
use App\Repositories\CourseRepository;
use App\Services\ProgressService;
use App\Models\User;

class LessonService
{
    protected $lessonRepo;
    protected $courseRepo;
    protected $progressService;

    public function __construct(
        LessonRepository $lessonRepo, 
        CourseRepository $courseRepo,
        ProgressService $progressService
    ) {
        $this->lessonRepo = $lessonRepo;
        $this->courseRepo = $courseRepo;
        $this->progressService = $progressService;
    }

    // --- منطق المدرب (CRUD) ---

    public function createLesson($user, array $data)
    {
        $course = $this->courseRepo->find($data['course_id'] ?? null);

        if (!$course) {
            return ['error' => 'Course not found.', 'code' => 404];
        }

        // التحقق من الملكية
        if ($course->institution->user_id !== $user->id) {
            return ['error' => 'Unauthorized.', 'code' => 403];
        }

        $lesson = $this->lessonRepo->create($data);

        return ['success' => true, 'data' => $lesson];
    }

    public function updateLesson($user, $lessonId, array $data)
    {
        $lesson = $this->lessonRepo->find($lessonId);

        if (!$lesson) {
            return ['error' => 'Lesson not found.', 'code' => 404];
        }

        if ($lesson->course->institution->user_id !== $user->id) {
            return ['error' => 'Unauthorized.', 'code' => 403];
        }

        $this->lessonRepo->update($lesson, $data);

        return ['success' => true, 'data' => $lesson];
    }

    // --- منطق الطالب (Interaction) ---

    public function markAsCompleted(User $user, $lessonId)
    {
        $lesson = $this->lessonRepo->find($lessonId);

        if (!$lesson) {
            return ['error' => 'Lesson not found.', 'code' => 404];
        }

        // التحقق: هل الطالب مسجل في دورة تحتوي هذا الدرس؟
        $isEnrolled = $user->cohorts()->where('course_id', $lesson->course_id)->exists();
        
        if (!$isEnrolled) {
            return ['error' => 'You are not enrolled in this course.', 'code' => 403];
        }

        // 1. تسجيل الإكمال (عبر Repository)
        $this->lessonRepo->markAsCompleted($user, $lesson);

        // 2. تحديث النسبة المئوية للدورة (عبر ProgressService)
        $progress = $this->progressService->updateCourseProgress($user, $lesson->course_id);

        return [
            'success' => true, 
            'message' => 'Lesson marked as completed.',
            'new_progress' => $progress . '%'
        ];
    }
    
    // جلب دروس لطالب مسجل
    public function getStudentLessons($user, $cohortId)
    {
        $cohort = $user->cohorts()->where('cohort_id', $cohortId)->first();
        
        if (!$cohort) {
             return ['error' => 'Not enrolled.', 'code' => 403];
        }
        
        $lessons = $this->lessonRepo->getLessonsByCourse($cohort->course_id);
        
        return ['success' => true, 'data' => $lessons];
    }
}