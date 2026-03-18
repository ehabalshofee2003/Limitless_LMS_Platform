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

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'duration_minutes' => $this->duration_minutes,
            'is_completed' => (bool) $isCompleted, // ترجع true أو false
            'resource_url' => $this->resource_path, // رابط الفيديو أو الملف
        ];
    }
}
