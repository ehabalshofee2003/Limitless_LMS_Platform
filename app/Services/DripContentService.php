<?php

namespace App\Services;

use App\Models\User;
use App\Models\Lesson;
use App\Models\Cohort;
use App\Repositories\LessonRepository;

class DripContentService
{
    protected $lessonRepo;

    public function __construct(LessonRepository $lessonRepo)
    {
        $this->lessonRepo = $lessonRepo;
    }

    /**
     * فتح الدرس الأول للطالب عند التسجيل
     */
    public function unlockFirstLesson(User $user, Cohort $cohort)
    {
        $firstLesson = $cohort->course->lessons()->orderBy('order')->first();
        
        if ($firstLesson) {
            $this->setUnlockStatus($user, $firstLesson, true);
        }
    }

    /**
     * التحقق مما إذا كان يجب فتح الدرس التالي
     * تُستدعى هذه الدالة بعد إكمال درس أو اجتياز اختبار
     */
    public function checkAndUnlockNext(User $user, Cohort $cohort, Lesson $completedLesson)
    {
        // 1. التحقق من استراتيجية الدفعة
        if ($cohort->content_unlock_strategy !== 'sequential') {
            return; // إذا ليست تسلسلية، لا نفعل شيئاً (المدرب يتحكم يدوياً أو الكل مفتوح)
        }

        // 2. التحقق من شروط الدرس الحالي
        if (!$this->meetsUnlockConditions($user, $completedLesson, $cohort)) {
            return; // لم يستوفِ الشروط بعد
        }

        // 3. جلب الدرس التالي
        $nextLesson = $cohort->course->lessons()
                                ->where('order', '>', $completedLesson->order)
                                ->orderBy('order')
                                ->first();

        if ($nextLesson) {
            $this->setUnlockStatus($user, $nextLesson, true);
        }
    }

    /**
     * التحقق من الشروط (مشاهدة 80% + اجتياز الاختبار)
     */
    protected function meetsUnlockConditions(User $user, Lesson $lesson, Cohort $cohort)
    {
        // أ. شرط المشاهدة
        $progress = $user->lessonProgress()->where('lesson_id', $lesson->id)->first();
        
        $watchCondition = false;
        if ($progress && $lesson->duration_minutes > 0) {
            $watchPercentage = ($progress->watch_seconds / ($lesson->duration_minutes * 60)) * 100;
            if ($watchPercentage >= $cohort->watch_threshold) {
                $watchCondition = true;
            }
        }

        // ب. شرط الاختبار (إذا كان للدرس اختبار)
        $quizCondition = true; // نفترض صحة إذا لم يكن هناك اختبار
        if ($lesson->quiz) {
            $passedAttempt = $lesson->quiz->attempts()
                ->where('user_id', $user->id)
                ->where('passed', true)
                ->exists();
            $quizCondition = $passedAttempt;
        }

        return $watchCondition && $quizCondition;
    }

    /**
     * تعيين حالة الفتح في قاعدة البيانات
     */
    protected function setUnlockStatus(User $user, Lesson $lesson, bool $status)
    {
        $user->lessonProgress()->updateOrCreate(
            ['lesson_id' => $lesson->id],
            ['is_unlocked' => $status]
        );
    }
    
    /**
     * فتح يدوي (للمدرب)
     */
    public function manualUnlock(User $user, Lesson $lesson)
    {
        $this->setUnlockStatus($user, $lesson, true);
    }
}