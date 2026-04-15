<?php

namespace App\Http\Resources\Master;

use App\Helpers\ImageHelper;
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
            'file_path' => ImageHelper::getImageUrl($this->file_path),
            'order_number' => $this->order_number,
            'duration_in_minutes' => $this->duration_in_minutes,
            'is_preview' => $this->is_preview,
            'quizzes' => $this->when(
                $this->relationLoaded('quizzes'),
                fn() => $this->quizzes->map(fn($quiz) => [
                    'id' => $quiz->id,
                    'title' => $quiz->title,
                    'description' => $quiz->description,
                    'type' => $quiz->type,
                    'time_limit' => $quiz->time_limit,
                    'minimum_score' => $quiz->minimum_score,
                    'total_questions' => $quiz->total_questions,
                ])
            ),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
