<?php

namespace App\Http\Resources\Master;

use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'thumbnail' => ImageHelper::getImageUrl($this->thumbnail),
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'mentor' => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => ImageHelper::getImageUrl($this->user->avatar),
            ] : null,
            'level' => $this->level,
            'status' => $this->status,
            'price' => (int) $this->price,
            'is_certificate' => $this->is_certificate,
            'lesson_count' => $this->lessons?->count() ?? 0,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
