<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPoint extends Model
{
    protected $fillable = [
        'student_id',
        'points_source',
        'source_id',
        'points',
        'gained_at',
    ];

    protected $casts = [
        'gained_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
