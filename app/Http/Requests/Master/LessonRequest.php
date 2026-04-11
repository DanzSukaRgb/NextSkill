<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class LessonRequest extends FormRequest
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
            'title' => $titleRule,
            'content' => ['nullable', 'string'],
            'vidio_url' => ['nullable', 'url'],
            'file_path' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip'],
            'order_number' => ['nullable', 'integer', 'min:1'],
            'duration_in_minutes' => ['nullable', 'integer', 'min:1'],
            'is_preview' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul lesson harus diisi.',
            'title.string' => 'Judul lesson harus berupa teks.',
            'title.max' => 'Judul lesson maksimal 255 karakter.',
            'content.string' => 'Konten lesson harus berupa teks.',
            'vidio_url.url' => 'URL video harus berupa URL yang valid.',
            'file_path.file' => 'File harus berupa file.',
            'file_path.max' => 'File maksimal 10 MB.',
            'file_path.mimes' => 'File harus berupa: pdf, doc, docx, xls, xlsx, ppt, pptx, zip.',
            'order_number.integer' => 'Nomor urut harus berupa angka.',
            'order_number.min' => 'Nomor urut minimal 1.',
            'duration_in_minutes.integer' => 'Durasi harus berupa angka.',
            'duration_in_minutes.min' => 'Durasi minimal 1 menit.',
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

            $validator->errors()->add('request', 'Minimal satu field harus dikirim untuk update lesson.');
        });
    }
}
