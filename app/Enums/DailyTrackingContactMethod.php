<?php

namespace App\Enums;

enum DailyTrackingContactMethod: string
{
    case GOOGLE = 'google';
    case PAGINA = 'pagina';
    case LLAMADA = 'llamada';
    case CAMBACEO = 'cambaceo';

    public function label(): string
    {
        return match ($this) {
            self::GOOGLE => 'Google',
            self::PAGINA => 'Pagina web',
            self::LLAMADA => 'Llamada',
            self::CAMBACEO => 'Cambaceo',
        };
    }
}
