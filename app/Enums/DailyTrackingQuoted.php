<?php

namespace App\Enums;

enum DailyTrackingQuoted: string
{
    case YES = 'yes';
    case PENDING = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::YES => 'Si',
            self::PENDING => 'Pendiente',
        };
    }
}
