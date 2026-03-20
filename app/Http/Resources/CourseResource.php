<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'status' => $this->status,
            'institution' => $this->whenLoaded('institution')->name ?? 'N/A',
            'created_at' => $this->created_at->diffForHumans(),
            'average_rating' => round($this->reviews()->avg('rating'), 1), // مثال: 4.5
            'reviews_count' => $this->reviews()->count(),
        ];
    }
}