<?php

namespace App\Enums;

enum PositionRisks: string
{
    case CLASS_I = '1';
    case CLASS_II = '2';
    case CLASS_III = '3';
    case CLASS_IV = '4';
    case CLASS_V = '5';
    case NOT_APPLICABLE = '99';

    public function name(): string
    {
        return match($this) {
            self::CLASS_I => 'Clase I',
            self::CLASS_II => 'Clase II',
            self::CLASS_III => 'Clase III',
            self::CLASS_IV => 'Clase IV',
            self::CLASS_V => 'Clase V',
            self::NOT_APPLICABLE => 'No aplica',
        };
    }

    public function isApplicable(): bool
    {
        return $this !== self::NOT_APPLICABLE;
    }

    public function isNotApplicable(): bool
    {
        return $this === self::NOT_APPLICABLE;
    }

    public function getNumericValue(): int
    {
        return match($this) {
            self::CLASS_I => 1,
            self::CLASS_II => 2,
            self::CLASS_III => 3,
            self::CLASS_IV => 4,
            self::CLASS_V => 5,
            self::NOT_APPLICABLE => 99,
        };
    }

    public function isHigherThan(self $other): bool
    {
        return $this->getNumericValue() > $other->getNumericValue();
    }

    public function isLowerThan(self $other): bool
    {
        return $this->getNumericValue() < $other->getNumericValue();
    }

    public static function getDescriptions(): array
    {
        $descriptions = [];
        foreach (self::cases() as $case) {
            $descriptions[$case->value] = $case->name();
        }
        return $descriptions;
    }

    public static function getApplicableClasses(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isApplicable();
        });
    }

    public static function isValid(string $code): bool
    {
        return !is_null(self::tryFrom($code));
    }

    public static function getDescriptionByCode(string $code): ?string
    {
        $classType = self::tryFrom($code);
        return $classType ? $classType->name() : null;
    }

    public static function getByNumericRange(int $min = 1, int $max = 5): array
    {
        return array_filter(self::cases(), function ($case) use ($min, $max) {
            $numericValue = $case->getNumericValue();
            return $numericValue >= $min && $numericValue <= $max;
        });
    }
}