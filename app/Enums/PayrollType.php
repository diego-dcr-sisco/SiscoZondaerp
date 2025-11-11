<?php

namespace App\Enums;

enum PayrollType: string
{
    case ORDINARY = 'O';
    case EXTRAORDINARY = 'E';

    public function name(): string
    {
        return match($this) {
            self::ORDINARY => 'Ordinaria',
            self::EXTRAORDINARY => 'Extraordinaria',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::ORDINARY => 'Nómina ordinaria periódica',
            self::EXTRAORDINARY => 'Nómina por pagos extraordinarios',
        };
    }

    public static function toArray(): array
    {
        return [
            self::ORDINARY->value => self::ORDINARY->label(),
            self::EXTRAORDINARY->value => self::EXTRAORDINARY->label(),
        ];
    }

    public static function options(): array
    {
        return array_map(function ($case) {
            return [
                'value' => $case->value,
                'label' => $case->label(),
                'description' => $case->description(),
            ];
        }, self::cases());
    }
}