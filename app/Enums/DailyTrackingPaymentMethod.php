<?php

namespace App\Enums;

enum DailyTrackingPaymentMethod: string
{
    case CASH = 'cash';
    case TRANSFER = 'transfer';
    case CHECK = 'check';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Efectivo',
            self::TRANSFER => 'Transferencia',
            self::CHECK => 'Cheque',
            self::OTHER => 'Otro',
        };
    }
}
