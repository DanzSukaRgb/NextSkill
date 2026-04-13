<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class GradeTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'score' => 'required|integer|min:0|max:100',
            'feedback' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'score.required' => 'Nilai wajib diisi',
            'score.integer' => 'Nilai harus berupa angka',
            'score.min' => 'Nilai minimal 0',
            'score.max' => 'Nilai maksimal 100',
        ];
    }
}
