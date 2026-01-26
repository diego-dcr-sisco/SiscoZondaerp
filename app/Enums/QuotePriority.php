<?php

namespace App\Enums;

enum QuotePriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    // Método para obtener opciones para selects
    public function label(): string
    {
        return match($this) {
            self::LOW => 'Baja',
            self::MEDIUM => 'Media',
            self::HIGH => 'Alta',
            self::URGENT => 'Urgente',
        };
    }

    // Método para colores (opcional)
    public function color(): string
    {
        return match($this) {
            self::LOW => 'gray',
            self::MEDIUM => 'blue',
            self::HIGH => 'orange',
            self::URGENT => 'red',
        };
    }

    public function class(): string
    {
        return match($this) {
            self::LOW => 'text-success',
            self::MEDIUM => 'text-warning',
            self::HIGH => 'text-orange',
            self::URGENT => 'text-danger',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::LOW => 'bi-arrow-down-circle',
            self::MEDIUM => 'bi-arrow-right-circle',
            self::HIGH => 'bi-arrow-up-circle',
            self::URGENT => 'bi-exclamation-triangle-fill',
        };
    }
}