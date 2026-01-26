<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case SINGLE_PAYMENT = 'PUE';
    case INSTALLMENTS_OR_DEFERRED = 'PPD';

    public function description(): string
    {
        return match($this) {
            self::SINGLE_PAYMENT => 'Pago en una sola exhibición',
            self::INSTALLMENTS_OR_DEFERRED => 'Pago en parcialidades ó diferido',
        };
    }

    public function isSinglePayment(): bool
    {
        return $this === self::SINGLE_PAYMENT;
    }

    public function isInstallmentsOrDeferred(): bool
    {
        return $this === self::INSTALLMENTS_OR_DEFERRED;
    }

    public function allowsInstallments(): bool
    {
        return $this === self::INSTALLMENTS_OR_DEFERRED;
    }

    public function requiresPaymentPlan(): bool
    {
        return $this === self::INSTALLMENTS_OR_DEFERRED;
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
        $paymentMethod = self::tryFrom($code);
        return $paymentMethod ? $paymentMethod->description() : null;
    }

    public static function getSinglePayment(): self
    {
        return self::SINGLE_PAYMENT;
    }

    public static function getInstallmentsOrDeferred(): self
    {
        return self::INSTALLMENTS_OR_DEFERRED;
    }
}