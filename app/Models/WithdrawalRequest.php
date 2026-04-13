<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'withdrawal_method',
        'bank_name',
        'account_number',
        'account_holder_name',
        'e_wallet_type',
        'e_wallet_number',
        'status',
        'requested_at',
        'approved_at',
        'approved_by',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
