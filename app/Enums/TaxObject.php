<?php

namespace App\Enums;

enum TaxObject: string
{
    case NOT_TAX_OBJECT = '01';
    case TAX_OBJECT_WITH_BREAKDOWN = '02';
    case TAX_OBJECT_WITHOUT_BREAKDOWN = '03';
    case TAX_OBJECT_NO_TAX = '04';

    public function description(): string
    {
        return match($this) {
            self::NOT_TAX_OBJECT => 'No objeto de impuesto',
            self::TAX_OBJECT_WITH_BREAKDOWN => '(Sí objeto de impuesto), se deben desglosar los Impuestos a nivel de Concepto',
            self::TAX_OBJECT_WITHOUT_BREAKDOWN => '(Sí objeto del impuesto y no obligado al desglose) no se desglosan impuestos a nivel Concepto',
            self::TAX_OBJECT_NO_TAX => '(Sí Objeto de impuesto y no causa impuesto)',
        };
    }

    public function isTaxObject(): bool
    {
        return match($this) {
            self::NOT_TAX_OBJECT => false,
            self::TAX_OBJECT_WITH_BREAKDOWN => true,
            self::TAX_OBJECT_WITHOUT_BREAKDOWN => true,
            self::TAX_OBJECT_NO_TAX => true,
        };
    }

    public function requiresTaxBreakdown(): bool
    {
        return $this === self::TAX_OBJECT_WITH_BREAKDOWN;
    }

    public function causesTax(): bool
    {
        return match($this) {
            self::NOT_TAX_OBJECT => false,
            self::TAX_OBJECT_WITH_BREAKDOWN => true,
            self::TAX_OBJECT_WITHOUT_BREAKDOWN => true,
            self::TAX_OBJECT_NO_TAX => false,
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
        $taxObject = self::tryFrom($code);
        return $taxObject ? $taxObject->description() : null;
    }

    public static function getTaxObjects(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isTaxObject();
        });
    }

    public static function getNonTaxObjects(): array
    {
        return array_filter(self::cases(), function ($case) {
            return !$case->isTaxObject();
        });
    }
}