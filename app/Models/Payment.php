<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'cfdi_type',
        'facturama_token',
        'UUID',
        'folio',
        'serie',
        'expedition_place',
        'receiver_name',
        'receiver_rfc',
        'receiver_cfdi_use',
        'receiver_fiscal_regime',
        'receiver_tax_zip_code',
        'status',
        'stamped_at',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'cfdi_type' => 'P',
    ];

    /**
     * Scope a query to only include payments with a specific CFDI type.
     */
    public function scopeCfdiType($query, $type)
    {
        return $query->where('cfdi_type', $type);
    }

    /**
     * Scope a query to only include payments with a specific serie.
     */
    public function scopeSerie($query, $serie)
    {
        return $query->where('serie', $serie);
    }

    /**
     * Scope a query to only include payments with a specific receiver RFC.
     */
    public function scopeReceiverRfc($query, $rfc)
    {
        return $query->where('receiver_rfc', $rfc);
    }

    /**
     * Get the full folio (serie-folio).
     */
    public function getFullFolioAttribute(): string
    {
        return $this->serie . '-' . $this->folio;
    }

    /**
     * Get the expedition place with format.
     */
    public function getExpeditionPlaceFormattedAttribute(): string
    {
        return "C.P. {$this->expedition_place}";
    }

    /**
     * Check if the payment has receiver information.
     */
    public function hasReceiverInfo(): bool
    {
        return !empty($this->receiver_name) &&
            !empty($this->receiver_rfc) &&
            !empty($this->receiver_cfdi_use);
    }

    /**
     * Get receiver information as array.
     */
    public function getReceiverInfoAttribute(): array
    {
        return [
            'name' => $this->receiver_name,
            'rfc' => $this->receiver_rfc,
            'cfdi_use' => $this->receiver_cfdi_use,
            'fiscal_regime' => $this->receiver_fiscal_regime,
            'tax_zip_code' => $this->receiver_tax_zip_code,
        ];
    }

    /**
     * Create a new payment with the given data.
     */
    public static function createPayment(array $data): self
    {
        return self::create([
            'invoice_id' => $data['invoice_id'],
            'cfdi_type' => $data['cfdi_type'] ?? 'P',
            'folio' => $data['folio'],
            'serie' => $data['serie'],
            'expedition_place' => $data['expedition_place'],
            'receiver_name' => $data['receiver_name'] ?? null,
            'receiver_rfc' => $data['receiver_rfc'] ?? null,
            'receiver_cfdi_use' => $data['receiver_cfdi_use'] ?? null,
            'receiver_fiscal_regime' => $data['receiver_fiscal_regime'] ?? null,
            'receiver_tax_zip_code' => $data['receiver_tax_zip_code'] ?? null,
        ]);
    }

    /**
     * Update payment with new data.
     */
    public function updatePayment(array $data): bool
    {
        return $this->update([
            'cfdi_type' => $data['cfdi_type'] ?? $this->cfdi_type,
            'folio' => $data['folio'] ?? $this->folio,
            'serie' => $data['serie'] ?? $this->serie,
            'expedition_place' => $data['expedition_place'] ?? $this->expedition_place,
            'receiver_name' => $data['receiver_name'] ?? $this->receiver_name,
            'receiver_rfc' => $data['receiver_rfc'] ?? $this->receiver_rfc,
            'receiver_cfdi_use' => $data['receiver_cfdi_use'] ?? $this->receiver_cfdi_use,
            'receiver_fiscal_regime' => $data['receiver_fiscal_regime'] ?? $this->receiver_fiscal_regime,
            'receiver_tax_zip_code' => $data['receiver_tax_zip_code'] ?? $this->receiver_tax_zip_code,
        ]);
    }

    /**
     * Get payments by invoice ID.
     */
    public static function getByInvoiceId($invoiceId)
    {
        return self::where('invoice_id', $invoiceId)->get();
    }

    /**
     * Get payments by receiver RFC.
     */
    public static function getByReceiverRfc($rfc)
    {
        return self::where('receiver_rfc', $rfc)->get();
    }

    /**
     * Get the latest payment folio for a serie.
     */
    public static function getLatestFolio($serie): string
    {
        $latest = self::where('serie', $serie)
            ->orderBy('folio', 'desc')
            ->first();

        return $latest ? $latest->folio : '000000';
    }

    /**
     * Generate next folio for a serie.
     */
    public static function generateNextFolio($serie): string
    {
        $latestFolio = self::getLatestFolio($serie);
        $nextNumber = intval($latestFolio) + 1;

        return str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function items()
    {
        return $this->hasMany(PaymentItem::class);
    }

    public function getStatus()
    {
        return match ((string) $this->status) {
            'paid' => 'Pagada',
            '1' => 'Pendiente',
            '2' => 'Vencida',
            '3' => 'Cancelada',
            '4' => 'Vigente',
            '5' => 'Timbrada',
            '6' => 'Parcial',
            '7' => 'En Proceso',
            '8' => 'Enviada',
            '9' => 'Rechazada',
            default => 'Desconocido'
        };
    }
}