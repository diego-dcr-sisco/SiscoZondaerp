<?php

namespace App\Enums;

enum DailyTrackingInvoice: string
{
    case YES = 'yes';
    case NO = 'no';
    case NOT_APPLICABLE = 'not_applicable';

    public function label(): string
    {
        return match ($this) {
            self::NO => 'No',
            self::YES => 'Si',
            self::NOT_APPLICABLE => 'No aplica',
        };
    }
}
