<?php
namespace App\Enums;
enum UnitCode: string
{
    case PIECE = 'H87';
    case ELEMENT = 'EA';
    case SERVICE_UNIT = 'E48';
    case ACTIVITY = 'ACT';
    case KILOGRAM = 'KGM';
    case WORK = 'E51';
    case RATE = 'A9';
    case METER = 'MTR';
    case BULK_PACKAGE = 'AB';
    case BASE_BOX = 'BB';
    case KIT = 'KT';
    case SET = 'SET';
    case LITER = 'LTR';
    case BOX = 'XBX';
    case MONTH = 'MON';
    case HOUR = 'HUR';
    case SQUARE_METER = 'MTK';
    case EQUIPMENT = '11';
    case MILLIGRAM = 'MGM';
    case PACKAGE = 'XPK';
    case KIT_PIECES = 'XKI';
    case VARIETY = 'AS';
    case GRAM = 'GRM';
    case PAIR = 'PR';
    case DOZEN_PIECES = 'DPC';
    case UNIT = 'xun';
    case DAY = 'DAY';
    case BATCH = 'XLT';
    case GROUPS = '10';
    case MILLILITER = 'MLT';
    case TRIP = 'E54';

    public function description(): string
    {
        return match($this) {
            self::PIECE => 'Pieza',
            self::ELEMENT => 'Elemento',
            self::SERVICE_UNIT => 'Unidad de Servicio',
            self::ACTIVITY => 'Actividad',
            self::KILOGRAM => 'Kilogramo',
            self::WORK => 'Trabajo',
            self::RATE => 'Tarifa',
            self::METER => 'Metro',
            self::BULK_PACKAGE => 'Paquete a granel',
            self::BASE_BOX => 'Caja base',
            self::KIT => 'Kit',
            self::SET => 'Conjunto',
            self::LITER => 'Litro',
            self::BOX => 'Caja',
            self::MONTH => 'Mes',
            self::HOUR => 'Hora',
            self::SQUARE_METER => 'Metro cuadrado',
            self::EQUIPMENT => 'Equipos',
            self::MILLIGRAM => 'Miligramo',
            self::PACKAGE => 'Paquete',
            self::KIT_PIECES => 'Kit (Conjunto de piezas)',
            self::VARIETY => 'Variedad',
            self::GRAM => 'Gramo',
            self::PAIR => 'Par',
            self::DOZEN_PIECES => 'Docenas de piezas',
            self::UNIT => 'Unidad',
            self::DAY => 'Día',
            self::BATCH => 'Lote',
            self::GROUPS => 'Grupos',
            self::MILLILITER => 'Mililitro',
            self::TRIP => 'Viaje',
        };
    }

    public static function getDescriptions(): array
    {
        $descriptions = [];
        foreach (self::cases() as $case) {
            $descriptions[$case->value] = $case->description();
        }
        return $descriptions;
    }

    public static function isValid(string $code): bool
    {
        return !is_null(self::tryFrom($code));
    }

    public static function getDescriptionByCode(string $code): ?string
    {
        $unit = self::tryFrom($code);
        return $unit ? $unit->description() : null;
    }
}