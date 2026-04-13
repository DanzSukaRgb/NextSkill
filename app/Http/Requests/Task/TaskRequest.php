<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'nullable|exists:lessons,id',
            'title' => 'required|string|min:3|max:255',
            'description' => 'nullable|string',
            'instruction' => 'nullable|string',
            'due_date' => 'nullable|date',
            'task_scope' => 'required|in:lesson,final',
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'Kursus wajib dipilih',
            'course_id.exists' => 'Kursus tidak ditemukan',
            'title.required' => 'Judul task wajib diisi',
            'title.min' => 'Judul harus minimal 3 karakter',
            'task_scope.required' => 'Jenis task wajib dipilih',
            'task_scope.in' => 'Jenis task tidak valid',
            'due_date.date' => 'Format tanggal tidak valid',
        ];
    }
}
