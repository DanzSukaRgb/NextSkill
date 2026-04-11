<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CourseRequest extends FormRequest
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
        $titleRule = $this->isMethod('post')
            ? ['required', 'string', 'max:255']
            : ['sometimes', 'required', 'string', 'max:255'];

        return [
            'category_id' => $this->isMethod('post') ? ['required', 'uuid', 'exists:categories,id'] : ['sometimes', 'uuid', 'exists:categories,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'title' => $titleRule,
            'thumbnail' => ['nullable', 'image', 'max:5120', 'mimes:jpeg,png,jpg,webp'],
            'description' => ['nullable', 'string'],
            'level' => ['nullable', 'in:beginner,intermediate,advanced'],
            'status' => ['nullable', 'in:draft,pending,published,rejected'],
            'is_certificate' => ['nullable', 'boolean'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori harus dipilih.',
            'category_id.uuid' => 'Format kategori tidak valid.',
            'category_id.exists' => 'Kategori tidak ditemukan.',
            'user_id.exists' => 'Mentor tidak ditemukan.',
            'title.required' => 'Judul kursus harus diisi.',
            'title.string' => 'Judul kursus harus berupa teks.',
            'title.max' => 'Judul kursus maksimal 255 karakter.',
            'thumbnail.image' => 'Thumbnail kursus harus berupa gambar.',
            'thumbnail.max' => 'Thumbnail kursus maksimal 5 MB.',
            'thumbnail.mimes' => 'Thumbnail kursus harus berupa file gambar (jpeg, png, jpg, webp).',
            'description.string' => 'Deskripsi kursus harus berupa teks.',
            'level.in' => 'Level kursus harus salah satu dari: beginner, intermediate, advanced.',
            'status.in' => 'Status kursus harus salah satu dari: draft, pending, published, rejected.',
            'price.numeric' => 'Harga kursus harus berupa angka.',
            'price.min' => 'Harga kursus tidak boleh negatif.',
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

            $validator->errors()->add('request', 'Minimal satu field harus dikirim untuk update kursus.');
        });
    }
}
