<?php

namespace App\Repositories;

use App\Models\Institution;

class InstitutionRepository
{
    // جلب كل المؤسسات (للمشرف)
    public function getAll()
    {
        return Institution::with('user')->get();
    }

    // إيجاد مؤسسة بالمعرف
    public function find($id)
    {
        return Institution::find($id);
    }

    // إنشاء مؤسسة جديدة
    public function create(array $data)
    {
        return Institution::create($data);
    }

    // تحديث مؤسسة
    public function update(Institution $institution, array $data)
    {
        return $institution->update($data);
    }

    // حذف مؤسسة
    public function delete(Institution $institution)
    {
        return $institution->delete();
    }
}