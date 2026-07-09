<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'target_amount',
        'collected_amount',
        'deadline',
        'video_url',
        'status',
        'rejection_reason',
        'settled_at',
    ];

    protected $appends = ['image_url'];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'collected_amount' => 'decimal:2',
        'deadline' => 'date',
        'settled_at' => 'datetime',
        'status' => CampaignStatus::class,
    ];

    protected static function booted(): void
    {
        static::saving(function (Campaign $campaign) {
            if ($campaign->exists && $campaign->isDirty('status')) {
                $original = self::resolveStatus($campaign->getOriginal('status'));
                $new = self::resolveStatus($campaign->status);

                if ($original && $new && !$original->canTransitionTo($new)) {
                    abort(422, "Transisi status dari {$original->value} ke {$new->value} tidak diizinkan.");
                }
            }
        });
    }

    protected static function resolveStatus(mixed $value): ?CampaignStatus
    {
        if ($value instanceof CampaignStatus) {
            return $value;
        }

        return CampaignStatus::tryFrom((string) $value);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->images->sortByDesc('is_primary')->first()?->image_url;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(CampaignTier::class);
    }

    public function backings(): HasMany
    {
        return $this->hasMany(Backing::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(CampaignUpdate::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(CampaignImage::class);
    }
}
