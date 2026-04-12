<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLevel extends Model
{
    protected $fillable = [
        'user_id',
        'level',
        'total_xp',
        'xp_for_next_level',
    ];

    protected $casts = [
        'level' => 'integer',
        'total_xp' => 'integer',
        'xp_for_next_level' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
