<?php
namespace App\Enums;

enum QuoteStatus: string
{
    case DRAFT = 'draft';         // Borrador
    case SENT = 'sent';           // Enviada al cliente
    case REVIEWED = 'reviewed';   // Revisada por cliente
    case APPROVED = 'approved';   // Aprobada por cliente
    case REJECTED = 'rejected';   // Rechazada por cliente
    case EXPIRED = 'expired';     // Expirada
    case CONVERTED = 'converted'; // Convertida a orden/pedido

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Borrador',
            self::SENT => 'Enviada',
            self::REVIEWED => 'Revisada',
            self::APPROVED => 'Aprobada',
            self::REJECTED => 'Rechazada',
            self::EXPIRED => 'Expirada',
            self::CONVERTED => 'Convertida',
        };
    }

    // Transiciones permitidas
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::DRAFT => [self::SENT, self::REJECTED],
            self::SENT => [self::REVIEWED, self::APPROVED, self::REJECTED, self::EXPIRED],
            self::REVIEWED => [self::APPROVED, self::REJECTED],
            self::APPROVED => [self::CONVERTED],
            default => [],
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'bi-file-earmark-text',
            self::SENT => 'bi-send-check',
            self::REVIEWED => 'bi-clipboard2-check',
            self::APPROVED => 'bi-check-circle',
            self::REJECTED => 'bi-x-circle',
            self::EXPIRED => 'bi-clock-history',
            self::CONVERTED => 'bi-arrow-repeat',
        };
    }

    public function class(): string
    {
        return match ($this) {
            self::DRAFT => 'text-dark',
            self::SENT => 'text-primary',
            self::REVIEWED => 'text-warning',
            self::APPROVED => 'text-success',
            self::REJECTED => 'text-danger',
            self::EXPIRED => 'text-secondary',
            self::CONVERTED => 'text-info',
        };
    }
}