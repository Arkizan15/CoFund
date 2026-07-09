<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case DRAFT = 'draft';
    case REVIEW = 'review';
    case ACTIVE = 'active';
    case SUCCESS = 'success';
    case FAILED = 'failed';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::DRAFT => [self::REVIEW],
            self::REVIEW => [self::ACTIVE, self::DRAFT, self::FAILED],
            self::ACTIVE => [self::SUCCESS, self::FAILED],
            self::SUCCESS => [],
            self::FAILED => [],
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions(), true);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
