<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class RevenueShareRequest extends FormRequest
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
            'commission_percentage' => 'sometimes|numeric|min:0|max:100',
            'mentor_revenue_share' => 'sometimes|numeric|min:0|max:100',
            'platform_revenue_share' => 'sometimes|numeric|min:0|max:100',
            'min_withdrawal_amount' => 'sometimes|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'commission_percentage.sometimes' => 'Persentase komisi wajib diisi.',
            'commission_percentage.numeric' => 'Persentase komisi harus berupa angka.',
            'commission_percentage.min' => 'Persentase komisi tidak boleh kurang dari 0%.',
            'commission_percentage.max' => 'Persentase komisi tidak boleh lebih dari 100%.',
            'mentor_revenue_share.sometimes' => 'Pembagian pendapatan mentor wajib diisi.',
            'mentor_revenue_share.numeric' => 'Pembagian pendapatan mentor harus berupa angka.',
            'mentor_revenue_share.min' => 'Pembagian pendapatan mentor tidak boleh kurang dari 0%.',
            'mentor_revenue_share.max' => 'Pembagian pendapatan mentor tidak boleh lebih dari 100%.',
            'platform_revenue_share.sometimes' => 'Pembagian pendapatan platform wajib diisi.',
            'platform_revenue_share.numeric' => 'Pembagian pendapatan platform harus berupa angka.',
            'platform_revenue_share.min' => 'Pembagian pendapatan platform tidak boleh kurang dari 0%.',
            'platform_revenue_share.max' => 'Pembagian pendapatan platform tidak boleh lebih dari 100%.',
            'min_withdrawal_amount.sometimes' => 'Jumlah minimum penarikan wajib diisi.',
            'min_withdrawal_amount.integer' => 'Jumlah minimum penarikan harus berupa angka bulat.',
            'min_withdrawal_amount.min' => 'Jumlah minimum penarikan tidak boleh kurang dari 0.',
        ];
    }
}
