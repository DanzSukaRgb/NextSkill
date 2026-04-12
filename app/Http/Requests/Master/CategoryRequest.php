<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $nameRule = $this->isMethod('post')
            ? ['required', 'string', 'max:255']
            : ['sometimes', 'required', 'string', 'max:255'];

        return [
            'name' => $nameRule,
            'icon' => ['nullable', 'max:2048'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori harus diisi.',
            'name.string' => 'Nama kategori harus berupa string.',
            'name.max' => 'Nama kategori maksimal 255 karakter.',
            'icon.max' => 'Ikon kategori maksimal 2 MB.',
            'description.string' => 'Deskripsi kategori harus berupa string.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        if ($this->isMethod('post')) {
            return;
        }

        $validator->after(function (Validator $validator) {
            if (count($this->all()) > 0) {
                return;
            }

            $validator->errors()->add('request', 'Minimal satu field harus dikirim untuk update kategori.');
        });
    }
}
