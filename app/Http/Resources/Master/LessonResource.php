<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
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
            'course_id' => $this->course_id,
            'title' => $this->title,
            'content' => $this->content,
            'vidio_url' => $this->vidio_url,
            'file_path' => $this->file_path ? asset('storage/' . $this->file_path) : null,
            'order_number' => $this->order_number,
            'duration_in_minutes' => $this->duration_in_minutes,
            'is_preview' => $this->is_preview,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
