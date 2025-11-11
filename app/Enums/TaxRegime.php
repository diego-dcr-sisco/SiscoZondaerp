<?php

namespace App\Enums;

enum TaxRegime: string
{
    case GENERAL_LEGAL_ENTITIES = '601';
    case NON_PROFIT_ENTITIES = '603';
    case SALARIES = '605';
    case RENTAL = '606';
    case SALE_ACQUISITION_GOODS = '607';
    case OTHER_INCOMES = '608';
    case FOREIGN_RESIDENTS = '610';
    case DIVIDENDS = '611';
    case BUSINESS_PROFESSIONAL_ACTIVITIES = '612';
    case INTEREST_INCOMES = '614';
    case PRIZES = '615';
    case NO_TAX_OBLIGATIONS = '616';
    case COOPERATIVE_SOCIETIES = '620';
    case INCORPORATION = '621';
    case AGRICULTURAL_ACTIVITIES = '622';
    case OPTIONAL_GROUP_SOCIETIES = '623';
    case COORDINATED = '624';
    case PLATFORM_BUSINESS_ACTIVITIES = '625';
    case SIMPLIFIED_TRUST = '626';

    public function name(): string
    {
        return match($this) {
            self::GENERAL_LEGAL_ENTITIES => 'General de Ley Personas Morales',
            self::NON_PROFIT_ENTITIES => 'Personas Morales con Fines no Lucrativos',
            self::SALARIES => 'Sueldos y Salarios e Ingresos Asimilados a Salarios',
            self::RENTAL => 'Arrendamiento',
            self::SALE_ACQUISITION_GOODS => 'Régimen de Enajenación o Adquisición de Bienes',
            self::OTHER_INCOMES => 'Demás ingresos',
            self::FOREIGN_RESIDENTS => 'Residentes en el Extranjero sin Establecimiento Permanente en México',
            self::DIVIDENDS => 'Ingresos por Dividendos (socios y accionistas)',
            self::BUSINESS_PROFESSIONAL_ACTIVITIES => 'Personas Físicas con Actividades Empresariales y Profesionales',
            self::INTEREST_INCOMES => 'Ingresos por intereses',
            self::PRIZES => 'Régimen de los ingresos por obtención de premios',
            self::NO_TAX_OBLIGATIONS => 'Sin obligaciones fiscales',
            self::COOPERATIVE_SOCIETIES => 'Sociedades Cooperativas de Producción que optan por diferir sus ingresos',
            self::INCORPORATION => 'Incorporación Fiscal',
            self::AGRICULTURAL_ACTIVITIES => 'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras',
            self::OPTIONAL_GROUP_SOCIETIES => 'Opcional para Grupos de Sociedades',
            self::COORDINATED => 'Coordinados',
            self::PLATFORM_BUSINESS_ACTIVITIES => 'Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas',
            self::SIMPLIFIED_TRUST => 'Régimen Simplificado de Confianza',
        };
    }

    public function isForNaturalPersons(): bool
    {
        return match($this) {
            self::GENERAL_LEGAL_ENTITIES,
            self::NON_PROFIT_ENTITIES,
            self::COOPERATIVE_SOCIETIES,
            self::AGRICULTURAL_ACTIVITIES,
            self::OPTIONAL_GROUP_SOCIETIES,
            self::COORDINATED => false,
            
            self::SALARIES,
            self::RENTAL,
            self::SALE_ACQUISITION_GOODS,
            self::OTHER_INCOMES,
            self::DIVIDENDS,
            self::BUSINESS_PROFESSIONAL_ACTIVITIES,
            self::INTEREST_INCOMES,
            self::PRIZES,
            self::NO_TAX_OBLIGATIONS,
            self::INCORPORATION,
            self::PLATFORM_BUSINESS_ACTIVITIES => true,

            self::FOREIGN_RESIDENTS,
            self::SIMPLIFIED_TRUST => true, // Ambos
        };
    }

    public function isForLegalEntities(): bool
    {
        return match($this) {
            self::GENERAL_LEGAL_ENTITIES,
            self::NON_PROFIT_ENTITIES,
            self::COOPERATIVE_SOCIETIES,
            self::AGRICULTURAL_ACTIVITIES,
            self::OPTIONAL_GROUP_SOCIETIES,
            self::COORDINATED => true,
            
            self::SALARIES,
            self::RENTAL,
            self::SALE_ACQUISITION_GOODS,
            self::OTHER_INCOMES,
            self::DIVIDENDS,
            self::BUSINESS_PROFESSIONAL_ACTIVITIES,
            self::INTEREST_INCOMES,
            self::PRIZES,
            self::NO_TAX_OBLIGATIONS,
            self::INCORPORATION,
            self::PLATFORM_BUSINESS_ACTIVITIES => false,

            self::FOREIGN_RESIDENTS,
            self::SIMPLIFIED_TRUST => true, // Ambos
        };
    }

    public function isForBoth(): bool
    {
        return $this->isForNaturalPersons() && $this->isForLegalEntities();
    }

    public function isSimplified(): bool
    {
        return $this === self::SIMPLIFIED_TRUST;
    }

    public function isForForeignResidents(): bool
    {
        return $this === self::FOREIGN_RESIDENTS;
    }

    public function isForPlatformActivities(): bool
    {
        return $this === self::PLATFORM_BUSINESS_ACTIVITIES;
    }

    public function hasNoTaxObligations(): bool
    {
        return $this === self::NO_TAX_OBLIGATIONS;
    }

    public function isForIncorporation(): bool
    {
        return $this === self::INCORPORATION;
    }

    public function isForSalaries(): bool
    {
        return $this === self::SALARIES;
    }

    public function isForBusinessActivities(): bool
    {
        return $this === self::BUSINESS_PROFESSIONAL_ACTIVITIES;
    }

    public function toArray(): array
    {
        return [
            'Natural' => $this->isForNaturalPersons(),
            'Moral' => $this->isForLegalEntities(),
            'Name' => $this->name(),
            'Value' => $this->value,
        ];
    }

    public static function getDescriptions(): array
    {
        $descriptions = [];
        foreach (self::cases() as $case) {
            $descriptions[$case->value] = $case->name();
        }
        return $descriptions;
    }

    public static function getForNaturalPersons(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isForNaturalPersons();
        });
    }

    public static function getForLegalEntities(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isForLegalEntities();
        });
    }

    public static function getForBoth(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isForBoth();
        });
    }

    public static function getSimplified(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isSimplified();
        });
    }

    public static function getOriginalArray(): array
    {
        $regimes = [];
        foreach (self::cases() as $case) {
            $regimes[] = $case->toArray();
        }
        return $regimes;
    }

    public static function isValid(string $code): bool
    {
        return !is_null(self::tryFrom($code));
    }

    public static function getByName(string $name): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->name() === $name) {
                return $case;
            }
        }
        return null;
    }

    public static function getDescriptionByCode(string $code): ?string
    {
        $regime = self::tryFrom($code);
        return $regime ? $regime->name() : null;
    }
}