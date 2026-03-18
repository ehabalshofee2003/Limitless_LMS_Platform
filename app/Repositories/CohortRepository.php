<?php

namespace App\Repositories;

use App\Models\Cohort;
use App\Models\User;

class CohortRepository
{
    // إيجاد دفعة
    public function find($id)
    {
        return Cohort::find($id);
    }

    // إنشاء دفعة جديدة
    public function create(array $data)
    {
        return Cohort::create($data);
    }

    // تحديث دفعة
    public function update(Cohort $cohort, array $data)
    {
        return $cohort->update($data);
    }

    // التحقق إذا كان الطالب مسجلاً مسبقاً
    public function isUserEnrolled($userId, $cohortId): bool
    {
        return Cohort::whereHas('students', fn($q) => $q->where('user_id', $userId))
                     ->where('id', $cohortId)
                     ->exists();
    }

    // حساب عدد الطلاب الحاليين
    public function getStudentsCount($cohortId): int
    {
        return Cohort::find($cohortId)->students()->count();
    }

    // تسجيل الطالب في الدفعة (عملية Insert في جدول cohort_user)
    public function enrollUser(Cohort $cohort, User $user)
    {
        return $cohort->students()->attach($user->id, [
            'progress_percentage' => 0,
            'enrolled_at' => now(),
            'certificate_issued' => false
        ]);
    }
    
    // جلب طلاب الدفعة (لتقارير المدرب)
    public function getCohortStudents($cohortId)
    {
        return Cohort::find($cohortId)->students()->withPivot(['progress_percentage', 'enrolled_at'])->get();
    }
}