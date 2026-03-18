<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CohortResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_date' => $this->start_date->format('Y-m-d H:i'),
            'end_date' => $this->end_date->format('Y-m-d H:i'),
            'google_meet_link' => $this->google_meet_link, // قد تحتاج لإخفائه لغير المسجلين
            'current_students' => $this->students()->count(),
            'max_students' => $this->max_students,
        ];
    }
}
