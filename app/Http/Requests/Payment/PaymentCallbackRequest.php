<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class PaymentCallbackRequest extends FormRequest
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
            'order_id' => 'required|string',
            'status_code' => 'required|string',
            'gross_amount' => 'required|string',
            'signature_key' => 'required|string',
            'transaction_status' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'Order ID tidak boleh kosong.',
            'status_code.required' => 'Status kode transaksi tidak boleh kosong.',
            'gross_amount.required' => 'Jumlah tagihan tidak boleh kosong.',
            'signature_key.required' => 'Signature key tidak valid atau kosong.',
            'transaction_status.required' => 'Status transaksi tidak boleh kosong.',
        ];
    }
}
