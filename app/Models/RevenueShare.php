<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RevenueShare extends Model
{
    protected $fillable = [
        'commission_percentage',
        'mentor_revenue_share',
        'platform_revenue_share',
        'min_withdrawal_amount',
    ];

    protected $casts = [
        'commission_percentage' => 'float',
        'mentor_revenue_share' => 'float',
        'platform_revenue_share' => 'float',
        'min_withdrawal_amount' => 'integer',
    ];
}
