<?php

namespace App\Http\Resources\Payment;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'customer' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],
            'course' => [
                'id' => $this->course?->id,
                'title' => $this->course?->title,
            ],
            'gross_amount' => (int) $this->gross_amount,
            'gross_amount_formatted' => 'Rp ' . number_format($this->gross_amount, 0, ',', '.'),
            'status' => $this->status,
            'snap_token' => $this->snap_token,
            'payment_url' => $this->payment_url,
            'created_at' => $this->created_at->format('Y-m-d'),
            'created_at_full' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
