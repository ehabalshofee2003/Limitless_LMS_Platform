<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    public function toArray($request)
    {
        // نتحقق إذا كان الطالب الحالي قد أكمل الدرس
        // الـ relation 'users' يحتوي على سجل واحد فقط (الطالب الحالي)
    $isCompleted = $this->relationLoaded('users') && $this->users->isNotEmpty() 
                    ? $this->users->first()->pivot->is_completed 
                    : false;

    $isUnlocked = $this->resource->pivot->is_unlocked ?? false; 
    
    // إذا الدفعة تدعم الفتح التسلسلي وكان الدرس مقفلاً، أخفِ الرابط
    $resourceUrl = $isUnlocked ? $this->resource_path : null;

    return [
        'id' => $this->id,
        'title' => $this->title,
        'is_locked' => !$isUnlocked, // Frontend يظهر أيقونة القفل
        'resource_url' => $resourceUrl, // null إذا كان مقفلاً
    ];
    }
}
