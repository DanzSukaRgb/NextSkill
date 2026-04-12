<?php

namespace App\Http\Requests\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class MatchingQuestionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'mentor';
    }

    public function rules(): array
    {
        return [
            'pairs' => 'required|array|min:3',
            'pairs.*.left_text' => 'required|string|min:3',
            'pairs.*.right_text' => 'required|string|min:3',
        ];
    }

    public function messages(): array
    {
        return [
            'pairs.required' => 'Pasangan cocok wajib diisi',
            'pairs.min' => 'Minimal 3 pasangan harus ditambahkan',
            'pairs.*.left_text.required' => 'Teks kiri wajib diisi',
            'pairs.*.right_text.required' => 'Teks kanan wajib diisi',
        ];
    }
}
