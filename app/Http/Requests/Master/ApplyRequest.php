<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use PHPUnit\Logging\JUnit\TestRunnerExecutionFinishedSubscriber;

class ApplyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'motivation' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivation.required' => 'Motivasi harus diisi.',
            'motivation.string' => 'Motivasi harus berupa teks.',
            'motivation.max' => 'Motivasi maksimal 1000 karakter.',
        ];
    }
}
