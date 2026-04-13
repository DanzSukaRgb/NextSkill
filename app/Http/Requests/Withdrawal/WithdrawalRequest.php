<?php

namespace App\Http\Requests\Withdrawal;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:50000|max:999999999',
            'withdrawal_method' => 'required|in:bank,e_wallet',

            // Bank validation (required if method = bank)
            'bank_name' => 'required_if:withdrawal_method,bank|in:BRI,BCA,Mandiri',
            'account_number' => 'required_if:withdrawal_method,bank|string|max:50',
            'account_holder_name' => 'required_if:withdrawal_method,bank|string|max:100',

            // E-wallet validation (required if method = e_wallet)
            'e_wallet_type' => 'required_if:withdrawal_method,e_wallet|in:gopay,ovo,dana,shopepay',
            'e_wallet_number' => 'required_if:withdrawal_method,e_wallet|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Jumlah penarikan wajib diisi',
            'amount.min' => 'Minimal penarikan Rp 50.000',
            'amount.max' => 'Maksimal penarikan Rp 999.999.999',
            'withdrawal_method.required' => 'Metode penarikan wajib dipilih',
            'bank_name.required_if' => 'Nama bank wajib diisi jika memilih bank transfer',
            'bank_name.in' => 'Bank harus salah satu dari: BRI, BCA, atau Mandiri',
            'account_number.required_if' => 'Nomor rekening wajib diisi',
            'account_holder_name.required_if' => 'Nama pemilik rekening wajib diisi',
            'e_wallet_type.required_if' => 'Tipe e-wallet wajib dipilih',
            'e_wallet_type.in' => 'E-wallet harus salah satu dari: GoPay, OVO, Dana, atau ShopeePay',
            'e_wallet_number.required_if' => 'Nomor e-wallet wajib diisi',
        ];
    }
}
