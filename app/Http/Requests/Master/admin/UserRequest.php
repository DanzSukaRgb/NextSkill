<?php

namespace App\Http\Requests\Master\admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $userId = $this->route('id') ?? null;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'avatar' => ['nullable', 'mimes:png,jpg,jpeg,webp'],
            'password' => $this->method() === 'POST' ? 'required|string|min:6' : 'nullable|string|min:6',
            'bio' => 'nullable|string',
            'role' => 'required|in:student,mentor',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'avatar.mimes' => 'Avatar harus berupa file gambar (png, jpg, jpeg, webp).',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'role.required' => 'Role harus dipilih.',
            'role.in' => 'Role harus salah satu dari: student, mentor.',
        ];
    }
}
