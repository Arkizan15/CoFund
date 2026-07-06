<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'name',
        'min_amount',
        'quota',
        'remaining_quota',
        'reward_description',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'quota' => 'integer',
        'remaining_quota' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
