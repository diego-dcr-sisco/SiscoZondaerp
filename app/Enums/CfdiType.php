<?php

namespace App\Enums;

enum CfdiType: string
{
    case INCOME = 'I';
    case EXPENSE = 'E';
    case TRANSFER = 'T';
    case CREDIT_NOTE = 'N';
    case PAYMENT = 'P';

    public function description(): string
    {
        return match($this) {
            self::INCOME => 'Ingreso',
            self::EXPENSE => 'Egreso',
            self::TRANSFER => 'Traslado',
            self::CREDIT_NOTE => 'Nota de crédito',
            self::PAYMENT => 'Pago',
        };
    }

    public function isIncome(): bool
    {
        return $this === self::INCOME;
    }

    public function isExpense(): bool
    {
        return $this === self::EXPENSE;
    }

    public function isTransfer(): bool
    {
        return $this === self::TRANSFER;
    }

    public function isCreditNote(): bool
    {
        return $this === self::CREDIT_NOTE;
    }

    public function isPayment(): bool
    {
        return $this === self::PAYMENT;
    }

    public function isIssuable(): bool
    {
        return match($this) {
            self::INCOME,
            self::EXPENSE,
            self::TRANSFER => true,
            self::CREDIT_NOTE,
            self::PAYMENT => false,
        };
    }

    public function isReceivable(): bool
    {
        return match($this) {
            self::INCOME,
            self::CREDIT_NOTE => true,
            self::EXPENSE,
            self::TRANSFER,
            self::PAYMENT => false,
        };
    }

    public function affectsTaxes(): bool
    {
        return match($this) {
            self::INCOME,
            self::EXPENSE,
            self::CREDIT_NOTE => true,
            self::TRANSFER,
            self::PAYMENT => false,
        };
    }

    public function requiresRelatedCfdi(): bool
    {
        return match($this) {
            self::CREDIT_NOTE,
            self::PAYMENT => true,
            self::INCOME,
            self::EXPENSE,
            self::TRANSFER => false,
        };
    }

    public function getRelatedCfdiTypes(): array
    {
        return match($this) {
            self::CREDIT_NOTE => [self::INCOME, self::EXPENSE],
            self::PAYMENT => [self::INCOME, self::EXPENSE],
            self::INCOME,
            self::EXPENSE,
            self::TRANSFER => [],
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
        $cfdiType = self::tryFrom($code);
        return $cfdiType ? $cfdiType->description() : null;
    }

    public static function getIssuableTypes(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isIssuable();
        });
    }

    public static function getTaxAffectingTypes(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->affectsTaxes();
        });
    }

    public static function getTypesRequiringRelatedCfdi(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->requiresRelatedCfdi();
        });
    }
}