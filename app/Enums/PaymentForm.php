<?php

namespace App\Enums;

enum PaymentForm: string
{
    case CASH = '01';
    case NOMINATIVE_CHECK = '02';
    case ELECTRONIC_FUNDS_TRANSFER = '03';
    case CREDIT_CARD = '04';
    case ELECTRONIC_WALLET = '05';
    case ELECTRONIC_MONEY = '06';
    case PAYROLL_VOUCHERS = '08';
    case DATION_IN_PAYMENT = '12';
    case SUBROGATION_PAYMENT = '13';
    case CONSIGNMENT_PAYMENT = '14';
    case WAIVER = '15';
    case COMPENSATION = '17';
    case NOVATION = '23';
    case CONFUSION = '24';
    case DEBT_REMISSION = '25';
    case PRESCRIPTION = '26';
    case CREDITOR_SATISFACTION = '27';
    case DEBIT_CARD = '28';
    case SERVICES_CARD = '29';
    case ADVANCE_APPLICATION = '30';
    case INTERMEDIARIES = '31';
    case TO_BE_DEFINED = '99';

    public function description(): string
    {
        return match($this) {
            self::CASH => 'Efectivo',
            self::NOMINATIVE_CHECK => 'Cheque nominativo',
            self::ELECTRONIC_FUNDS_TRANSFER => 'Transferencia electrónica de fondos',
            self::CREDIT_CARD => 'Tarjeta de crédito',
            self::ELECTRONIC_WALLET => 'Monedero electrónico',
            self::ELECTRONIC_MONEY => 'Dinero electrónico',
            self::PAYROLL_VOUCHERS => 'Vales de despensa',
            self::DATION_IN_PAYMENT => 'Dación en pago',
            self::SUBROGATION_PAYMENT => 'Pago por subrogación',
            self::CONSIGNMENT_PAYMENT => 'Pago por consignación',
            self::WAIVER => 'Condonación',
            self::COMPENSATION => 'Compensación',
            self::NOVATION => 'Novación',
            self::CONFUSION => 'Confusión',
            self::DEBT_REMISSION => 'Remisión de deuda',
            self::PRESCRIPTION => 'Prescripción o caducidad',
            self::CREDITOR_SATISFACTION => 'A satisfacción del acreedor',
            self::DEBIT_CARD => 'Tarjeta de débito',
            self::SERVICES_CARD => 'Tarjeta de servicios',
            self::ADVANCE_APPLICATION => 'Aplicación de anticipos',
            self::INTERMEDIARIES => 'Intermediarios',
            self::TO_BE_DEFINED => 'Por definir',
        };
    }

    public function isElectronic(): bool
    {
        return match($this) {
            self::ELECTRONIC_FUNDS_TRANSFER,
            self::ELECTRONIC_WALLET,
            self::ELECTRONIC_MONEY => true,
            default => false
        };
    }

    public function isCard(): bool
    {
        return match($this) {
            self::CREDIT_CARD,
            self::DEBIT_CARD,
            self::SERVICES_CARD => true,
            default => false
        };
    }

    public function isCash(): bool
    {
        return $this === self::CASH;
    }

    public function isLegalMechanism(): bool
    {
        return match($this) {
            self::DATION_IN_PAYMENT,
            self::SUBROGATION_PAYMENT,
            self::CONSIGNMENT_PAYMENT,
            self::WAIVER,
            self::COMPENSATION,
            self::NOVATION,
            self::CONFUSION,
            self::DEBT_REMISSION,
            self::PRESCRIPTION,
            self::CREDITOR_SATISFACTION => true,
            default => false
        };
    }

    public function requiresBankDetails(): bool
    {
        return match($this) {
            self::NOMINATIVE_CHECK,
            self::ELECTRONIC_FUNDS_TRANSFER => true,
            default => false
        };
    }

    public function isCommon(): bool
    {
        return match($this) {
            self::CASH,
            self::CREDIT_CARD,
            self::DEBIT_CARD,
            self::ELECTRONIC_FUNDS_TRANSFER,
            self::ELECTRONIC_WALLET => true,
            default => false
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
        $paymentForm = self::tryFrom($code);
        return $paymentForm ? $paymentForm->description() : null;
    }

    public static function getElectronicMethods(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isElectronic();
        });
    }

    public static function getCardMethods(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isCard();
        });
    }

    public static function getCommonMethods(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isCommon();
        });
    }

    public static function getLegalMechanisms(): array
    {
        return array_filter(self::cases(), function ($case) {
            return $case->isLegalMechanism();
        });
    }
}