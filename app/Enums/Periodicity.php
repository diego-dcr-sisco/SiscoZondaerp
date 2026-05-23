<?php

namespace App\Enums;

enum Periodicity: string
{
    case DAILY = '01';
    case WEEKLY = '02';
    case BIWEEKLY = '03';
    case MONTHLY = '04';
    case BIMONTHLY = '05';

    public function description(): string
    {
        return match($this) {
            self::DAILY => 'Diario',
            self::WEEKLY => 'Semanal',
            self::BIWEEKLY => 'Quincenal',
            self::MONTHLY => 'Mensual',
            self::BIMONTHLY => 'Bimestral',
        };
    }

    public function days(): int
    {
        return match($this) {
            self::DAILY => 1,
            self::WEEKLY => 7,
            self::BIWEEKLY => 15,
            self::MONTHLY => 30,
            self::BIMONTHLY => 60,
        };
    }

    public function isDaily(): bool
    {
        return $this === self::DAILY;
    }

    public function isWeekly(): bool
    {
        return $this === self::WEEKLY;
    }

    public function isBiweekly(): bool
    {
        return $this === self::BIWEEKLY;
    }

    public function isMonthly(): bool
    {
        return $this === self::MONTHLY;
    }

    public function isBimonthly(): bool
    {
        return $this === self::BIMONTHLY;
    }

    public function isFrequent(): bool
    {
        return match($this) {
            self::DAILY,
            self::WEEKLY => true,
            self::BIWEEKLY,
            self::MONTHLY,
            self::BIMONTHLY => false,
        };
    }

    public function isInfrequent(): bool
    {
        return !$this->isFrequent();
    }

    public function getNextDate(\DateTime $fromDate): \DateTime
    {
        $nextDate = clone $fromDate;
        
        return match($this) {
            self::DAILY => $nextDate->modify('+1 day'),
            self::WEEKLY => $nextDate->modify('+1 week'),
            self::BIWEEKLY => $nextDate->modify('+2 weeks'),
            self::MONTHLY => $nextDate->modify('+1 month'),
            self::BIMONTHLY => $nextDate->modify('+2 months'),
        };
    }

    public function getYearlyOccurrences(): int
    {
        return match($this) {
            self::DAILY => 365,
            self::WEEKLY => 52,
            self::BIWEEKLY => 26,
            self::MONTHLY => 12,
            self::BIMONTHLY => 6,
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
        $periodicity = self::tryFrom($code);
        return $periodicity ? $periodicity->description() : null;
    }

    public static function getFrequent(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isFrequent();
        });
    }

    public static function getInfrequent(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isInfrequent();
        });
    }

    public static function getByMinimumDays(int $minDays): array
    {
        return array_filter(self::cases(), function ($case) use ($minDays) {
            return $case->days() >= $minDays;
        });
    }

    public static function getByMaximumDays(int $maxDays): array
    {
        return array_filter(self::cases(), function ($case) use ($maxDays) {
            return $case->days() <= $maxDays;
        });
    }
}