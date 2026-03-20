<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // مهم لاستخدام الـ Builder

class Course extends Model
{
    protected $fillable = [
        'institution_id', 'title', 'slug', 'description', 'price', 'status', 'approved_by'
    ];

    // المؤسسة المالكة للدورة
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    // الدفعات (Cohorts) التابعة لهذه الدورة
    public function cohorts()
    {
        return $this->hasMany(Cohort::class);
    }

    // الدروس التابعة للدورة
    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }
    
    /**
     * نطاق البحث العام (Search Scope)
     * يبحث في العنوان والوصف
     */
    public function scopeFilter(Builder $query, array $filters)
    {
        // 1. البحث النصي (Search)
        $query->when($filters['search'] ?? false, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                  ->orWhere('description', 'LIKE', '%' . $search . '%');
            });
        });

        // 2. فلترة السعر (Price Range)
        // ?price_min=10&price_max=100
        $query->when($filters['price_min'] ?? false, function ($query, $price) {
            $query->where('price', '>=', $price);
        });
        
        $query->when($filters['price_max'] ?? false, function ($query, $price) {
            $query->where('price', '<=', $price);
        });

        // 3. فلترة الحالة (للمشرف مثلاً أو لعرض المجاني فقط)
        // ?status=published
        $query->when($filters['status'] ?? false, function ($query, $status) {
            $query->where('status', $status);
        });
        
        // 4. ترتيب (Sorting)
        // ?sort=price_asc أو ?sort=created_desc
        $query->when($filters['sort'] ?? false, function ($query, $sort) {
            switch ($sort) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'popular':
                    // مثال: الترتيب حسب عدد الطلاب (يحتاج علاقة)
                    // $query->withCount('students')->orderBy('students_count', 'desc');
                    break;
                default:
                    $query->latest(); // الافتراضي: الأحدث
            }
        });

        return $query;
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
 