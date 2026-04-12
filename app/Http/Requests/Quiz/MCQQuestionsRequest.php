<?php

namespace App\Http\Requests\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class MCQQuestionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'mentor';
    }

    public function rules(): array
    {
        return [
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string|min:5',
            'questions.*.options' => 'required|array|min:2|max:6',
            'questions.*.options.*.text' => 'required|string|min:1',
            'questions.*.options.*.is_correct' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'questions.required' => 'Pertanyaan wajib diisi',
            'questions.min' => 'Minimal 1 pertanyaan harus ditambahkan',
            'questions.*.question.required' => 'Teks pertanyaan wajib diisi',
            'questions.*.options.required' => 'Opsi wajib ditambahkan (minimal 2)',
            'questions.*.options.min' => 'Minimal 2 opsi per pertanyaan',
            'questions.*.options.*.text.required' => 'Teks opsi wajib diisi',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $questions = $this->input('questions', []);

            foreach ($questions as $index => $question) {
                $hasCorrectOption = false;
                foreach ($question['options'] ?? [] as $option) {
                    if ($option['is_correct'] ?? false) {
                        $hasCorrectOption = true;
                        break;
                    }
                }

                if (!$hasCorrectOption) {
                    $validator->errors()->add(
                        "questions.{$index}.correct_option",
                        "Pertanyaan nomor " . ($index + 1) . " harus memiliki 1 opsi benar"
                    );
                }
            }
        });
    }
}
