<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class SubmitTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'submission_link' => 'required_without:submission_text|url',
            'submission_text' => 'required_without:submission_link|string',
        ];
    }

    public function messages(): array
    {
        return [
            'submission_link.required_without' => 'Link GitHub atau teks submission wajib diisi',
            'submission_link.url' => 'Format URL tidak valid',
            'submission_text.required_without' => 'Teks atau link wajib diisi',
        ];
    }
}
