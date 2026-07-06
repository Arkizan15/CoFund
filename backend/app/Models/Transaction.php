<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'backing_id',
        'type',
        'amount',
        'status',
        'reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'type' => 'string',
        'status' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function backing(): BelongsTo
    {
        return $this->belongsTo(Backing::class);
    }
}
