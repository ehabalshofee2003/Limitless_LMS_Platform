<?php

namespace App\Services;

use App\Repositories\InstitutionRepository;
use Illuminate\Support\Str;

class InstitutionService
{
    protected $institutionRepo;

    public function __construct(InstitutionRepository $institutionRepo)
    {
        $this->institutionRepo = $institutionRepo;
    }

    // إنشاء مؤسسة (مع التحقق من عدم التكرار)
    public function createInstitution($user, array $data)
    {
        // منطق العمل: لا يمكن للمستخدم إنشاء أكثر من مؤسسة
        if ($user->institution) {
            return ['error' => 'You already have an institution profile.', 'code' => 409];
        }

        $data['user_id'] = $user->id;
        $data['slug'] = Str::slug($data['name']);
        $data['is_verified'] = false; // يحتاج موافقة المشرف

        $institution = $this->institutionRepo->create($data);

        return ['success' => true, 'data' => $institution];
    }

    // تحديث مؤسسة (مع التحقق من الملكية)
    public function updateInstitution($userId, $institutionId, array $data)
    {
        $institution = $this->institutionRepo->find($institutionId);

        if (!$institution) {
            return ['error' => 'Institution not found.', 'code' => 404];
        }

        // منطق العمل: فقط المالك يمكنه التعديل
        if ($institution->user_id !== $userId) {
            return ['error' => 'Unauthorized action.', 'code' => 403];
        }

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $this->institutionRepo->update($institution, $data);

        return ['success' => true, 'data' => $institution];
    }

    // جلب لوحة التحكم
    public function getDashboard($id)
    {
        $institution = $this->institutionRepo->find($id);
        
        if (!$institution) {
            return ['error' => 'Not found', 'code' => 404];
        }

        // تحميل العلاقات للإحصائيات
        $institution->load('courses');
        
        return ['success' => true, 'data' => $institution];
    }
}