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
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\CodeRunnerController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\AnalyticsController;

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

        // التحقق من الإيميل
        Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
            ->middleware(['signed'])
            ->name('verification.verify');
    });

    // الدورات (قراءة فقط)
    Route::get('courses', [CourseController::class, 'index']);
    Route::get('courses/{course}', [CourseController::class, 'show']);
    
    // التعليقات (قراءة)
    Route::get('lessons/{lesson}/comments', [CommentController::class, 'indexByLesson']);
    Route::get('courses/{course}/comments', [CommentController::class, 'indexByCourse']);

    // Webhooks
    Route::post('webhooks/stripe', [WebhookController::class, 'handleStripe']);

    // ========== 2. المسارات المحمية (Protected Routes) ==========
    
    Route::middleware('auth:sanctum')->group(function () {

        // --- المصادقة والملف الشخصي ---
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('profile', [AuthController::class, 'profile']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
            Route::post('refresh-token', [AuthController::class, 'refreshToken']);
            Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
                ->middleware(['throttle:6,1']);
        });

        // --- تسجيل الجهاز للإشعارات (FCM) ---
        Route::post('devices/register', [AuthController::class, 'registerDevice']);

        // --- المحفظة (Wallet) ---
        Route::prefix('wallet')->group(function () {
            Route::get('/balance', [WalletController::class, 'balance']);
            Route::get('/transactions', [WalletController::class, 'transactions']);
        });

        // --- الإشعارات ---
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        });

        // --- التقييمات (Reviews) ---
        Route::post('courses/{course}/reviews', [ReviewController::class, 'store']);

        // --- تشغيل الأكواد ---
        Route::post('/run-code', [CodeRunnerController::class, 'run']);

        // ========== مسارات الطلاب (Student) ==========
        Route::middleware(['role:student', 'verified'])->group(function () {
            // التسجيل في الدفعات
            Route::post('cohorts/{cohort}/enroll', [EnrollmentController::class, 'enroll']);
            Route::get('my-cohorts', [CohortController::class, 'myEnrollments']);
            
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
            
            // التعليقات
            Route::post('comments', [CommentController::class, 'store']);
        });

        // ========== مسارات المدرب/المؤسسة (Instructor) ==========
        Route::middleware(['role:institution'])->group(function () {
            // المؤسسة
            Route::apiResource('institutions', InstitutionController::class)->only(['store', 'update', 'show']);
            
            // الدورات
            Route::apiResource('courses', CourseController::class)->only(['store', 'update', 'destroy']);
            Route::post('courses/{course}/publish', [CourseController::class, 'publish']);
            Route::post('courses/{course}/new-version', [CourseController::class, 'createVersion']); // نظام الإصدارات
            
            // الدفعات
            Route::apiResource('cohorts', CohortController::class)->only(['store', 'update']);
            Route::post('cohorts/{cohort}/unlock-strategy', [CohortController::class, 'updateUnlockStrategy']); // نظام Drip
            Route::post('cohorts/{cohort}/manual-unlock', [CohortController::class, 'manualUnlockLesson']); // فتح يدوي
            Route::get('cohorts/{cohort}/students', [CohortController::class, 'getStudents']);
            
            // الدروس
            Route::apiResource('lessons', LessonController::class)->only(['store', 'update', 'destroy']);
            Route::post('lessons/upload', [LessonController::class, 'uploadResource']);
            
            // الاختبارات
            Route::post('quizzes', [QuizController::class, 'store']);
            
            // المحفظة والسحب
            Route::post('/wallet/payout', [WalletController::class, 'requestPayout']);
            Route::get('/wallet/payouts', [WalletController::class, 'payoutHistory']);
        });

        // ========== مسارات المشرف (Admin) ==========
        Route::middleware(['role:super_admin'])->group(function () {
            Route::get('admin/analytics/dashboard', [AnalyticsController::class, 'dashboard']);
            Route::get('admin/analytics/revenue', [AnalyticsController::class, 'revenue']);
            Route::post('admin/institutions/{id}/approve', [InstitutionController::class, 'approve']);
        });
    });
});