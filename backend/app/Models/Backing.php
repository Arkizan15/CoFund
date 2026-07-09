<?php

namespace App\Models;

use App\Enums\BackingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'campaign_id',
        'tier_id',
        'amount',
        'status',
        'external_id',
        'invoice_url',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => BackingStatus::class,
    ];

    protected static function booted(): void
    {
        static::saving(function (Backing $backing) {
            if ($backing->exists && $backing->isDirty('status')) {
                $original = self::resolveStatus($backing->getOriginal('status'));
                $new = self::resolveStatus($backing->status);

                if ($original && $new && !$original->canTransitionTo($new)) {
                    abort(422, "Transisi status dari {$original->value} ke {$new->value} tidak diizinkan.");
                }
            }
        });
    }

    protected static function resolveStatus(mixed $value): ?BackingStatus
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof BackingStatus) {
            return $value;
        }

        if ($value instanceof \BackedEnum) {
            return BackingStatus::tryFrom($value->value);
        }

        return BackingStatus::tryFrom((string) $value);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(CampaignTier::class, 'tier_id');
    }
}
