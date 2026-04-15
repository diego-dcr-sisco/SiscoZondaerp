<?php

namespace App\Enums;

enum DailyTrackingClosed: string
{
    case YES = 'yes';
    case NO = 'no';
    case PENDING = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::YES => 'Si',
            self::NO => 'No',
            self::PENDING => 'Pendiente',
        };
    }
}
