<?php

namespace App\Enums;

enum BackingStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case REFUNDED = 'refunded';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING => [self::COMPLETED, self::REFUNDED],
            self::COMPLETED => [self::REFUNDED],
            self::REFUNDED => [],
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
