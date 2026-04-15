<?php

namespace App\Enums;

enum DailyTrackingCustomerType: string
{
    case DOMESTICO = 'domestico';
    case COMERCIAL = 'comercial';
    case INDUSTRIAL = 'industrial';

    public function label(): string
    {
        return match ($this) {
            self::DOMESTICO => 'Domestico',
            self::COMERCIAL => 'Comercial',
            self::INDUSTRIAL => 'Industrial',
        };
    }
}
