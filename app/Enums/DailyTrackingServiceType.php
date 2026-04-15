<?php

namespace App\Enums;

enum DailyTrackingServiceType: string
{
    case INDUSTRIAL = 'industrial';
    case COMERCIAL = 'comercial';

    public function label(): string
    {
        return match ($this) {
            self::INDUSTRIAL => 'Industrial',
            self::COMERCIAL => 'Comercial',
        };
    }
}
