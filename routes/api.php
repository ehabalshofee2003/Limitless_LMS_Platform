<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CohortController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\InstitutionController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\AnalyticsController; // تأكد من وجودها
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReviewController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ========== 1. المسارات العامة (Public Routes) ==========
    
    // المصادقة (Auth)
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('request-password-reset', [AuthController::class, 'requestPasswordReset']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);

        // التحقق من الإيميل (يجب أن يكون قابلاً للوصول عبر الرابط المرسل)
        Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
            ->middleware(['signed']) // تم إزالة auth:sanctum ليتسنى فتح الرابط من الإيميل مباشرة
            ->name('verification.verify');
    });

    // الدورات (قراءة فقط للجميع)
    Route::get('courses', [CourseController::class, 'index']);
    Route::get('courses/{course}', [CourseController::class, 'show']);
    Route::get('courses/{course}/reviews', [ReviewController::class, 'index']);


    // Webhooks (للبوابك البنكية)
    Route::post('webhooks/stripe', [WebhookController::class, 'handleStripe']);

    // ========== 2. المسارات المحمية (Protected Routes) ==========
    
    Route::middleware(['auth:sanctum'])->group(function () {

        // --- الملف الشخصي والتحقق (لجميع المستخدمين) ---
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('profile', [AuthController::class, 'profile']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
            Route::post('refresh-token', [AuthController::class, 'refreshToken']);
            
            // إعادة إرسال رابط التحقق
            Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
                ->middleware(['throttle:6,1']);
        });

        // --- مسارات الطالب (Student) ---
        // الشرط: يجب أن يكون إيميله محققاً ('verified')
        Route::middleware(['role:student', 'verified'])->group(function () {
            
            // التسجيل في الدفعات
            Route::get('my-cohorts', [CohortController::class, 'myEnrollments']);
            Route::post('cohorts/{cohort}/enroll', [EnrollmentController::class, 'enroll']);
            
            // الدروس والتقدم
            Route::get('cohorts/{cohort}/lessons', [LessonController::class, 'byCohort']);
            Route::post('lessons/{lesson}/complete', [LessonController::class, 'complete']);
            
            // الاختبارات
            Route::get('quizzes/{quiz}', [QuizController::class, 'show']);
            Route::post('quizzes/{quiz}/submit', [QuizController::class, 'submit']);
            
            // الشهادات
            Route::get('cohorts/{cohort}/eligibility', [CertificateController::class, 'checkEligibility']);
            Route::get('cohorts/{cohort}/certificate', [CertificateController::class, 'download']);
            
            // المدفوعات
            Route::post('payments/checkout', [PaymentController::class, 'checkout']);
            Route::get('payments/history', [PaymentController::class, 'history']);
            Route::post('courses/{course}/reviews', [ReviewController::class, 'store']);

        });

        // --- مسارات المدرب/المؤسسة (Instructor) ---
        Route::middleware(['role:institution'])->group(function () {
            
            // إدارة المؤسسة
            Route::apiResource('institutions', InstitutionController::class)->only(['store', 'update', 'show']);
            
            // إدارة الدورات
            Route::apiResource('courses', CourseController::class)->only(['store', 'update', 'destroy']);
            Route::post('courses/{course}/publish', [CourseController::class, 'publish']);
            
            // إدارة الدفعات
            Route::apiResource('cohorts', CohortController::class)->only(['store', 'update']);
            Route::get('cohorts/{cohort}/students', [CohortController::class, 'getStudents']);
            
            // إدارة الدروس
            Route::apiResource('lessons', LessonController::class)->only(['store', 'update', 'destroy']);
            
            // إدارة الاختبارات
            Route::post('quizzes', [QuizController::class, 'store']); // أضفناها للتبسيط
            // داخل مجموعة role:institution
            Route::post('lessons/upload', [LessonController::class, 'uploadResource']); 
        });

        // --- مسارات المشرف (Admin) ---
        Route::middleware(['role:super_admin'])->group(function () {
            Route::get('admin/analytics/dashboard', [AnalyticsController::class, 'dashboard']);
            Route::post('admin/institutions/{id}/approve', [InstitutionController::class, 'approve']);
        });

    });
    
});
// داخل مجموعة auth:sanctum
Route::prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
});