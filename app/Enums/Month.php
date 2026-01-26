<?php

namespace App\Enums;

enum Month: string
{
    case JANUARY = '01';
    case FEBRUARY = '02';
    case MARCH = '03';
    case APRIL = '04';
    case MAY = '05';
    case JUNE = '06';
    case JULY = '07';
    case AUGUST = '08';
    case SEPTEMBER = '09';
    case OCTOBER = '10';
    case NOVEMBER = '11';
    case DECEMBER = '12';

    public function name(): string
    {
        return match($this) {
            self::JANUARY => 'Enero',
            self::FEBRUARY => 'Febrero',
            self::MARCH => 'Marzo',
            self::APRIL => 'Abril',
            self::MAY => 'Mayo',
            self::JUNE => 'Junio',
            self::JULY => 'Julio',
            self::AUGUST => 'Agosto',
            self::SEPTEMBER => 'Septiembre',
            self::OCTOBER => 'Octubre',
            self::NOVEMBER => 'Noviembre',
            self::DECEMBER => 'Diciembre',
        };
    }

    public function shortName(): string
    {
        return match($this) {
            self::JANUARY => 'Ene',
            self::FEBRUARY => 'Feb',
            self::MARCH => 'Mar',
            self::APRIL => 'Abr',
            self::MAY => 'May',
            self::JUNE => 'Jun',
            self::JULY => 'Jul',
            self::AUGUST => 'Ago',
            self::SEPTEMBER => 'Sep',
            self::OCTOBER => 'Oct',
            self::NOVEMBER => 'Nov',
            self::DECEMBER => 'Dic',
        };
    }

    public function englishName(): string
    {
        return match($this) {
            self::JANUARY => 'January',
            self::FEBRUARY => 'February',
            self::MARCH => 'March',
            self::APRIL => 'April',
            self::MAY => 'May',
            self::JUNE => 'June',
            self::JULY => 'July',
            self::AUGUST => 'August',
            self::SEPTEMBER => 'September',
            self::OCTOBER => 'October',
            self::NOVEMBER => 'November',
            self::DECEMBER => 'December',
        };
    }

    public function numericValue(): int
    {
        return match($this) {
            self::JANUARY => 1,
            self::FEBRUARY => 2,
            self::MARCH => 3,
            self::APRIL => 4,
            self::MAY => 5,
            self::JUNE => 6,
            self::JULY => 7,
            self::AUGUST => 8,
            self::SEPTEMBER => 9,
            self::OCTOBER => 10,
            self::NOVEMBER => 11,
            self::DECEMBER => 12,
        };
    }

    public function days(bool $leapYear = false): int
    {
        return match($this) {
            self::JANUARY => 31,
            self::FEBRUARY => $leapYear ? 29 : 28,
            self::MARCH => 31,
            self::APRIL => 30,
            self::MAY => 31,
            self::JUNE => 30,
            self::JULY => 31,
            self::AUGUST => 31,
            self::SEPTEMBER => 30,
            self::OCTOBER => 31,
            self::NOVEMBER => 30,
            self::DECEMBER => 31,
        };
    }

    public function quarter(): int
    {
        return match($this) {
            self::JANUARY, self::FEBRUARY, self::MARCH => 1,
            self::APRIL, self::MAY, self::JUNE => 2,
            self::JULY, self::AUGUST, self::SEPTEMBER => 3,
            self::OCTOBER, self::NOVEMBER, self::DECEMBER => 4,
        };
    }

    public function semester(): int
    {
        return match($this) {
            self::JANUARY, self::FEBRUARY, self::MARCH, self::APRIL, self::MAY, self::JUNE => 1,
            self::JULY, self::AUGUST, self::SEPTEMBER, self::OCTOBER, self::NOVEMBER, self::DECEMBER => 2,
        };
    }

    public function isFirstQuarter(): bool
    {
        return $this->quarter() === 1;
    }

    public function isLastQuarter(): bool
    {
        return $this->quarter() === 4;
    }

    public function isFirstSemester(): bool
    {
        return $this->semester() === 1;
    }

    public function isLastSemester(): bool
    {
        return $this->semester() === 2;
    }

    public function isWinter(): bool
    {
        $numericValue = $this->numericValue();
        return in_array($numericValue, [12, 1, 2]);
    }

    public function isSpring(): bool
    {
        $numericValue = $this->numericValue();
        return in_array($numericValue, [3, 4, 5]);
    }

    public function isSummer(): bool
    {
        $numericValue = $this->numericValue();
        return in_array($numericValue, [6, 7, 8]);
    }

    public function isAutumn(): bool
    {
        $numericValue = $this->numericValue();
        return in_array($numericValue, [9, 10, 11]);
    }

    public function next(): self
    {
        $currentNumeric = $this->numericValue();
        $nextNumeric = $currentNumeric === 12 ? 1 : $currentNumeric + 1;
        return self::from(sprintf('%02d', $nextNumeric));
    }

    public function previous(): self
    {
        $currentNumeric = $this->numericValue();
        $previousNumeric = $currentNumeric === 1 ? 12 : $currentNumeric - 1;
        return self::from(sprintf('%02d', $previousNumeric));
    }

    public function getRange(int $count): array
    {
        $months = [];
        $current = $this;
        
        for ($i = 0; $i < $count; $i++) {
            $months[] = $current;
            $current = $current->next();
        }
        
        return $months;
    }

    public function toDateTime(int $year): \DateTime
    {
        return new \DateTime("$year-{$this->numericValue()}-01");
    }

    public static function fromDateTime(\DateTimeInterface $date): self
    {
        $monthNumber = (int) $date->format('n');
        return self::from(sprintf('%02d', $monthNumber));
    }

    public static function fromNumeric(int $monthNumber): self
    {
        return self::from(sprintf('%02d', $monthNumber));
    }

    public static function current(): self
    {
        return self::fromDateTime(new \DateTime());
    }

    public static function getNames(): array
    {
        $names = [];
        foreach (self::cases() as $case) {
            $names[$case->value] = $case->name();
        }
        return $names;
    }

    public static function getShortNames(): array
    {
        $names = [];
        foreach (self::cases() as $case) {
            $names[$case->value] = $case->shortName();
        }
        return $names;
    }

    public static function getByQuarter(int $quarter): array
    {
        return array_filter(self::cases(), function ($case) use ($quarter) {
            return $case->quarter() === $quarter;
        });
    }

    public static function getBySemester(int $semester): array
    {
        return array_filter(self::cases(), function ($case) use ($semester) {
            return $case->semester() === $semester;
        });
    }

    public static function getBySeason(string $season): array
    {
        return array_filter(self::cases(), function ($case) use ($season) {
            return match($season) {
                'winter' => $case->isWinter(),
                'spring' => $case->isSpring(),
                'summer' => $case->isSummer(),
                'autumn' => $case->isAutumn(),
                default => false,
            };
        });
    }

    public static function isValid(string $month): bool
    {
        return !is_null(self::tryFrom($month));
    }

    public static function isValidNumeric(int $monthNumber): bool
    {
        return $monthNumber >= 1 && $monthNumber <= 12;
    }

    public static function getNameByCode(string $monthCode): ?string
    {
        $month = self::tryFrom($monthCode);
        return $month ? $month->name() : null;
    }

    public static function getNameByNumeric(int $monthNumber): ?string
    {
        $monthCode = sprintf('%02d', $monthNumber);
        return self::getNameByCode($monthCode);
    }
}