<?php

namespace App\Enums;

enum TaxpayerType: string
{
    case PHYSICAL = 'physical';
    case MORAL = 'moral';
    
    public function name(): string
    {
        return match($this) {
            self::PHYSICAL => 'Persona Física',
            self::MORAL => 'Persona Moral',
        };
    }
    
    public function description(): string
    {
        return match($this) {
            self::PHYSICAL => 'Persona física con actividad empresarial',
            self::MORAL => 'Persona moral o entidad legal',
        };
    }
}