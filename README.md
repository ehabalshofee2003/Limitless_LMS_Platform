🚀 Limitless LMS - Backend Documentation

نظام إدارة تعليمية متكامل (LMS) يعتمد على معمارية متقدمة، يدعم الدفعات التعليمية، الدفع الإلكتروني، الذكاء الاصطناعي، والإشعارات الفورية.

🏗️ معمارية المشروع (Architecture)

    1. Pattern: Repository & Service Layer Pattern (لضمان فصل المسؤوليات وسهولة الصيانة).
    2. Framework: Laravel 12 (PHP 8.2+).
    3. Database: MySQL.
    4. Performance: Redis Caching & Queues.
    5. Containerization: Docker Ready.

🗄️ هيكل قاعدة البيانات (Database Schema)


|table        |    Description
| :--- | :--- | 
|users        |  المستخدمين (طلاب، مدربين، مشرفين).
|institutions | ملفات المؤسسات والمدربين.
|courses	  |الدورات التعليمية (مع نظام الإصدارات Versioning).
|cohorts      |الدفعات (تاريخ البداية والنهاية، استراتيجية فتح المحتوى).
|lessons      |الدروس (فيديو، PDF، روابط).
|lesson_user  |تقدم الطالب في كل درس (نسبة المشاهدة، حالة الفتح).
|quizzes      |الاختبارات (أسئلة JSON).
|quiz_attempts|	محاولات الطالب ودرجاته.
|payments     |	سجل المدفوعات.
|wallets      |أرصدة المستخدمين (مدربين وطلاب).
|transactions |	سجل الحركات المالية (المعاملات).
|reviews      |	تقييمات الدورات.
|comments     |	التعليقات التشعبية (Threaded Comments).
|notifications|	الإشعارات الداخلية.
|fcm_tokens   |	رموز أجهزة الإشعارات (Firebase).

📡 API Reference (توثيق المسارات)

Base URL: /api/v1Auth Method: Bearer Token (Sanctum)

1. المصادقة (Authentication)

|Method	| Endpoint	| Description	| Auth
| :--- | :--- | :--- | :--- |
|POST	| `/auth/register`	| تسجيل مستخدم جديد.	| ❌
|POST	| `/auth/login`	| تسجيل الدخول.	| ❌
|POST	| `/auth/logout`	| تسجيل الخروج.	| ✅
|GET	| `/auth/profile`| عرض الملف الشخصي.	| ✅
|POST	| `/devices/register`	| تسجيل جهاز للإشعارات (FCM Token).	| ✅
 
 
2. الدورات والنسخ (Courses & Versioning)

| Method | Endpoint | Description | Role |
| :--- | :--- | :--- | :--- |
| GET | `/courses` | قائمة الدورات (مع بحث وفلترة). | Public |
| GET | `/courses/{id}` | تفاصيل دورة. | Public |
| POST | `/courses` | إنشاء دورة جديدة. | Institution |
| PUT | `/courses/{id}` | تعديل دورة. | Institution |
| POST | `/courses/{id}/publish` | طلب نشر دورة. | Institution |
| POST | `/courses/{id}/new-version` | إنشاء نسخة جديدة (Versioning). | Institution |

/courses?search=laravel&price_min=0&price_max=100&sort=price_asc

3. الدفعات والمحتوى (Cohorts & Drip Content)

| Method | Endpoint | Description | Role |
| :--- | :--- | :--- | :--- |
| POST | `/cohorts` | إنشاء دفعة جديدة. | Institution |
| POST | `/cohorts/{id}/enroll` | التسجيل في دفعة. | Student |
| GET | `/cohorts/{id}/lessons` | عرض دروس الدفعة (مع حالة الفتح). | Student |
| POST | `/cohorts/{id}/unlock-strategy` | تعديل طريقة فتح المحتوى. | Institution |


4. الدروس والتفاعل (Lessons & Interaction)

| Method | Endpoint | Description | Role |
| :--- | :--- | :--- | :--- |
| POST | `/lessons` | إنشاء درس. | Institution |
| POST | `/lessons/upload` | رفع ملف (فيديو/PDF). | Institution |
| POST | `/lessons/{id}/complete` | تسجيل إكمال درس وتحديث التقدم. | Student |

5. الاختبارات (Quizzes)

| Method | Endpoint | Description | Role |
| :--- | :--- | :--- | :--- |
| GET | `/quizzes/{id}` | عرض الأسئلة. | Student |
| POST | `/quizzes/{id}/submit` | إرسال الإجابات. | Student |

6. المحفظة والمدفوعات (Wallet & Payments)

| Method | Endpoint | Description | Role |
| :--- | :--- | :--- | :--- |
| GET | `/wallet/balance` | عرض الرصيد (متاح ومعلق). | All |
| GET | `/wallet/transactions` | سجل المعاملات. | All |
| POST | `/wallet/payout` | طلب سحب أرباح. | Institution |
| POST | `/payments/checkout` | بدء عملية دفع. | Student |

7. التعليقات والنقاشات (Comments)

| Method | Endpoint | Description | Auth |
| :--- | :--- | :--- | :--- |
| GET | `/lessons/{id}/comments` | عرض التعليقات (شجرة). | Public |
| POST | `/comments` | إضافة تعليق أو رد. | Auth |

8. التقييمات (Reviews)

| Method | Endpoint | Description | Role |
| :--- | :--- | :--- | :--- |
| GET | `/courses/{id}/reviews` | عرض التقييمات. | Public |
| POST | `/courses/{id}/reviews` | إضافة تقييم. | Student |

9. تشغيل الأكواد (Code Runner)

| Method | Endpoint | Description | Role |
| :--- | :--- | :--- | :--- |
| POST | `/run-code` | تنفيذ كود برمجي (Python, PHP...). | Student |
 

10. الشهادات (Certificates)

| Method | Endpoint | Description | Role |
| :--- | :--- | :--- | :--- |
| GET | `/cohorts/{id}/eligibility` | التحقق من أهلية الشهادة. | Student |
| GET | `/cohorts/{id}/certificate` | تحميل الشهادة (PDF). | Student |

11. الإشعارات (Notifications)

| Method | Endpoint | Description | Auth |
| :--- | :--- | :--- | :--- |
| GET | `/notifications` | قائمة الإشعارات. | ✅ |
| POST | `/notifications/{id}/read` | تعليم كمقروء. | ✅ |


