<?php

namespace App\Enums;

enum CfdiUsage: string
{
    case PAYROLL = 'CN01';
    case PAYMENTS = 'CP01';
    case MEDICAL_DENTAL_HOSPITAL = 'D01';
    case DISABILITY_MEDICAL_EXPENSES = 'D02';
    case FUNERAL_EXPENSES = 'D03';
    case DONATIONS = 'D04';
    case MORTGAGE_INTEREST = 'D05';
    case VOLUNTARY_SAR_CONTRIBUTIONS = 'D06';
    case HEALTH_INSURANCE_PREMIUMS = 'D07';
    case SCHOOL_TRANSPORTATION = 'D08';
    case SAVINGS_PENSION_PLANS = 'D09';
    case EDUCATIONAL_SERVICES = 'D10';
    case MERCHANDISE_ACQUISITION = 'G01';
    case RETURNS_DISCOUNTS = 'G02';
    case GENERAL_EXPENSES = 'G03';
    case CONSTRUCTIONS = 'I01';
    case OFFICE_FURNITURE_EQUIPMENT = 'I02';
    case TRANSPORT_EQUIPMENT = 'I03';
    case COMPUTER_EQUIPMENT = 'I04';
    case TOOLS_DIES_MOLDS = 'I05';
    case TELEPHONE_COMMUNICATIONS = 'I06';
    case SATELLITE_COMMUNICATIONS = 'I07';
    case OTHER_MACHINERY_EQUIPMENT = 'I08';
    case TO_BE_DEFINED = 'P01';
    case NO_TAX_EFFECTS = 'S01';

    public function name(): string
    {
        return match($this) {
            self::PAYROLL => 'Nómina',
            self::PAYMENTS => 'Pagos',
            self::MEDICAL_DENTAL_HOSPITAL => 'Honorarios médicos, dentales y gastos hospitalarios.',
            self::DISABILITY_MEDICAL_EXPENSES => 'Gastos médicos por incapacidad o discapacidad',
            self::FUNERAL_EXPENSES => 'Gastos funerales.',
            self::DONATIONS => 'Donativos.',
            self::MORTGAGE_INTEREST => 'Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación).',
            self::VOLUNTARY_SAR_CONTRIBUTIONS => 'Aportaciones voluntarias al SAR.',
            self::HEALTH_INSURANCE_PREMIUMS => 'Primas por seguros de gastos médicos.',
            self::SCHOOL_TRANSPORTATION => 'Gastos de transportación escolar obligatoria.',
            self::SAVINGS_PENSION_PLANS => 'Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones.',
            self::EDUCATIONAL_SERVICES => 'Pagos por servicios educativos (colegiaturas)',
            self::MERCHANDISE_ACQUISITION => 'Adquisición de mercancías',
            self::RETURNS_DISCOUNTS => 'Devoluciones, descuentos o bonificaciones',
            self::GENERAL_EXPENSES => 'Gastos en general',
            self::CONSTRUCTIONS => 'Construcciones',
            self::OFFICE_FURNITURE_EQUIPMENT => 'Mobiliario y equipo de oficina por inversiones',
            self::TRANSPORT_EQUIPMENT => 'Equipo de transporte',
            self::COMPUTER_EQUIPMENT => 'Equipo de cómputo y accesorios',
            self::TOOLS_DIES_MOLDS => 'Dados, troqueles, moldes, matrices y herramental',
            self::TELEPHONE_COMMUNICATIONS => 'Comunicaciones telefónicas',
            self::SATELLITE_COMMUNICATIONS => 'Comunicaciones satelitales',
            self::OTHER_MACHINERY_EQUIPMENT => 'Otra maquinaria y equipo',
            self::TO_BE_DEFINED => 'Por Definir',
            self::NO_TAX_EFFECTS => 'Sin efectos fiscales',
        };
    }

    public function category(): string
    {
        return match($this) {
            self::PAYROLL,
            self::PAYMENTS => 'nómina_pagos',
            
            self::MEDICAL_DENTAL_HOSPITAL,
            self::DISABILITY_MEDICAL_EXPENSES,
            self::FUNERAL_EXPENSES,
            self::DONATIONS,
            self::MORTGAGE_INTEREST,
            self::VOLUNTARY_SAR_CONTRIBUTIONS,
            self::HEALTH_INSURANCE_PREMIUMS,
            self::SCHOOL_TRANSPORTATION,
            self::SAVINGS_PENSION_PLANS,
            self::EDUCATIONAL_SERVICES => 'deducciones_personales',
            
            self::MERCHANDISE_ACQUISITION,
            self::RETURNS_DISCOUNTS,
            self::GENERAL_EXPENSES => 'gastos_generales',
            
            self::CONSTRUCTIONS,
            self::OFFICE_FURNITURE_EQUIPMENT,
            self::TRANSPORT_EQUIPMENT,
            self::COMPUTER_EQUIPMENT,
            self::TOOLS_DIES_MOLDS,
            self::TELEPHONE_COMMUNICATIONS,
            self::SATELLITE_COMMUNICATIONS,
            self::OTHER_MACHINERY_EQUIPMENT => 'inversiones',
            
            self::TO_BE_DEFINED,
            self::NO_TAX_EFFECTS => 'otros',
        };
    }

    public function isForNaturalPersons(): bool
    {
        return match($this) {
            self::MEDICAL_DENTAL_HOSPITAL,
            self::DISABILITY_MEDICAL_EXPENSES,
            self::FUNERAL_EXPENSES,
            self::DONATIONS,
            self::MORTGAGE_INTEREST,
            self::VOLUNTARY_SAR_CONTRIBUTIONS,
            self::HEALTH_INSURANCE_PREMIUMS,
            self::SCHOOL_TRANSPORTATION,
            self::SAVINGS_PENSION_PLANS,
            self::EDUCATIONAL_SERVICES,
            self::PAYROLL => true,
            
            default => false,
        };
    }

    public function isForLegalEntities(): bool
    {
        return match($this) {
            self::PAYROLL => false,
            self::MEDICAL_DENTAL_HOSPITAL,
            self::DISABILITY_MEDICAL_EXPENSES,
            self::FUNERAL_EXPENSES,
            self::DONATIONS,
            self::MORTGAGE_INTEREST,
            self::VOLUNTARY_SAR_CONTRIBUTIONS,
            self::HEALTH_INSURANCE_PREMIUMS,
            self::SCHOOL_TRANSPORTATION,
            self::SAVINGS_PENSION_PLANS,
            self::EDUCATIONAL_SERVICES => false,
            
            default => true,
        };
    }

    public function isForBoth(): bool
    {
        return $this->isForNaturalPersons() && $this->isForLegalEntities();
    }

    public function isDeduction(): bool
    {
        return str_starts_with($this->value, 'D');
    }

    public function isExpense(): bool
    {
        return str_starts_with($this->value, 'G');
    }

    public function isInvestment(): bool
    {
        return str_starts_with($this->value, 'I');
    }

    public function isPayroll(): bool
    {
        return str_starts_with($this->value, 'CN');
    }

    public function isPayment(): bool
    {
        return str_starts_with($this->value, 'CP');
    }

    public function hasTaxEffects(): bool
    {
        return $this !== self::NO_TAX_EFFECTS;
    }

    public function isDefined(): bool
    {
        return $this !== self::TO_BE_DEFINED;
    }

    public function toArray(): array
    {
        return [
            'Natural' => $this->isForNaturalPersons(),
            'Moral' => $this->isForLegalEntities(),
            'Name' => $this->name(),
            'Value' => $this->value,
            'Category' => $this->category(),
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

    public static function getDeductions(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isDeduction();
        });
    }

    public static function getExpenses(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isExpense();
        });
    }

    public static function getInvestments(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isInvestment();
        });
    }

    public static function getPayroll(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isPayroll();
        });
    }

    public static function getByCategory(string $category): array
    {
        return array_filter(self::cases(), function ($case) use ($category) {
            return $case->category() === $category;
        });
    }

    public static function getOriginalArray(): array
    {
        $usages = [];
        foreach (self::cases() as $case) {
            $usages[] = $case->toArray();
        }
        return $usages;
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
        $usage = self::tryFrom($code);
        return $usage ? $usage->name() : null;
    }
}