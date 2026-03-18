<?php

namespace App\Services;

use App\Models\User;
use App\Models\Lesson;
use App\Models\Cohort;
use Illuminate\Support\Facades\DB;

class ProgressService
{
    /**
     * تحديث نسبة التقدم الإجمالية للطالب في الدفعة
     */
    public function updateCourseProgress(User $user, Cohort $cohort)
    {
        // 1. جلب الدورة المرتبطة بالدفعة
        $course = $cohort->course;

        // 2. حساب إجمالي عدد الدروس في الدورة
        $totalLessons = $course->lessons()->count();

        if ($totalLessons === 0) {
            return 0; // لا توجد دروس
        }

        // 3. حساب عدد الدروس التي أكملها الطالب (من جدول lesson_user)
        $completedLessons = DB::table('lesson_user')
            ->where('user_id', $user->id)
            ->whereIn('lesson_id', $course->lessons->pluck('id'))
            ->where('is_completed', true)
            ->count();

        // 4. حساب النسبة المئوية
        $percentage = ($completedLessons / $totalLessons) * 100;

        // 5. تحديث جدول تسجيل الطالب (cohort_user)
        $cohort->students()->updateExistingPivot($user->id, [
            'progress_percentage' => round($percentage, 2)
        ]);

        return round($percentage, 2);
    }

    /**
     * تسجيل إكمال درس معين
     */
    public function markLessonAsCompleted(User $user, Lesson $lesson)
    {
        // استخدام updateOrCreate لتسجيل الإكمال أو تحديثه
        DB::table('lesson_user')->updateOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ],
            [
                'is_completed' => true,
                'watch_seconds' => $lesson->duration_minutes * 60, // نفترض أنه شاهده بالكامل
                'updated_at' => now()
            ]
        );

        return true;
    }
}