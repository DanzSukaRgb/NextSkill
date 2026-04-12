<?php

namespace App\Http\Requests\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class QuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'mentor';
    }

    public function rules(): array
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'nullable|exists:lessons,id',
            'title' => 'required|string|min:3|max:255',
            'description' => 'nullable|string',
            'instruction' => 'nullable|string',
            'type' => 'required|in:MCQ,Matching',
            'quiz_scope' => 'required|in:lesson,final',
            'time_limit' => 'nullable|integer|min:1',
            'minimum_score' => 'required|integer|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'Kursus wajib dipilih',
            'title.required' => 'Judul quiz wajib diisi',
            'type.required' => 'Tipe quiz wajib dipilih',
            'quiz_scope.required' => 'Cakupan quiz wajib dipilih',
            'minimum_score.required' => 'Nilai minimum wajib diisi',
        ];
    }
}
