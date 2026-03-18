<?php

namespace App\Services;

use App\Models\User;
use App\Models\Cohort;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateService
{
    /**
     * التحقق مما إذا كان الطالب يستوفي شروط الحصول على الشهادة
     */
    public function isEligible(User $user, Cohort $cohort): bool
    {
        // نجلب بيانات التسجيل (Pivot)
        $enrollment = $cohort->students()->where('user_id', $user->id)->first();

        if (!$enrollment) {
            return false; // غير مسجل
        }

        $data = $enrollment->pivot;

        // الشروط (يمكن تعديلها لاحقاً لتكون ديناميكية من إعدادات الدورة)
        
        // 1. شرط الحضور (نسبة إكمال الدروس)
        $attendancePassed = $data->progress_percentage >= 80;

        // 2. شرط الدرجة النهائية (في الاختبار النهائي)
        // نفترض أن 0 تعني أنه لم يختبر بعد، أو لم ينجز الاختبار النهائي
        $gradePassed = $data->final_exam_grade >= 60; 

        // 3. شرط تقييم المدرب (إذا كان هناك تقييم يدوي مطلوب)
        // هنا سأفترض أن الشرط هو أن يكون التقييم موجوداً وليس صفراً، أو يمكن تجاوزه مؤقتاً
        // $instructorApproved = !is_null($data->instructor_rating); 

        // النتيجة النهائية
        return $attendancePassed && $gradePassed;
    }

    /**
     * توليد ملف PDF للشهادة
     */
 public function generatePdf(User $user, Cohort $cohort)
{
    $data = [
        'student_name'   => $user->name,
        'course_name'    => $cohort->course->title,
        'date'           => now()->format('F d, Y'),
        'instructor_name'=> $cohort->course->institution->name ?? 'Limitless Academy',
        'cohort_id'      => $cohort->id,
    ];
    
    // استخدام loadView لتحميل القالب الجديد
    $pdf = Pdf::loadView('certificates.template', $data);
    
    // يمكن ضبط حجم الورق إذا لزم الأمر (الافتراضي A4)
    // $pdf->setPaper('A4', 'landscape'); 
    
    return $pdf->download('Limitless-Certificate-' . $user->name . '.pdf');
}
}