<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentsRelatedDocument extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payments_related_documents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payment_item_id',
        'invoice_id',
        'cfdi_uuid',
        'partiality_number',
        'folio',
        'serie',
        'payment_method',
        'previous_balance_amount',
        'amount_paid',
        'imp_saldo_insoluto',
        'tax_object',
        'tax_name',
        'tax_rate',
        'tax_total',
        'tax_base',
        'tax_is_retention',
        'tax_is_federal_tax',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    /*protected $casts = [
        'previous_balance_amount' => 'float',
        'amount_paid' => 'float',
        'imp_saldo_insoluto' => 'float',
        'tax_rate' => 'float',
        'tax_total' => 'float',
        'tax_base' => 'float',
        'tax_is_retention' => 'boolean',
        'tax_is_federal_tax' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];*/

    /**
     * The attributes with default values.
     *
     * @var array
     */
    protected $attributes = [
        'tax_object' => '02',
        'tax_name' => 'IVA',
        'tax_rate' => 0.16,
        'tax_total' => 0.16,
        'tax_is_retention' => false,
        'tax_is_federal_tax' => true,
    ];

    /**
     * Get the payment item that owns the related document.
     */
    public function paymentItem(): BelongsTo
    {
        return $this->belongsTo(PaymentItem::class);
    }

    /**
     * Accessor para el monto total (amount_paid + impuestos)
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->amount_paid + $this->tax_total;
    }

    /**
     * Accessor para el saldo anterior formateado
     */
    public function getFormattedPreviousBalanceAttribute(): string
    {
        return '$' . number_format($this->previous_balance_amount, 2);
    }

    /**
     * Accessor para el monto pagado formateado
     */
    public function getFormattedAmountPaidAttribute(): string
    {
        return '$' . number_format($this->amount_paid, 2);
    }

    /**
     * Accessor para el impuesto formateado
     */
    public function getFormattedTaxTotalAttribute(): string
    {
        return '$' . number_format($this->tax_total, 2);
    }

    /**
     * Scope para documentos con CFDI UUID
     */
    public function scopeWithCfdi($query)
    {
        return $query->whereNotNull('cfdi_uuid');
    }

    /**
     * Scope para documentos por método de pago
     */
    public function scopeByPaymentMethod($query, $paymentMethod)
    {
        return $query->where('payment_method', $paymentMethod);
    }

    /**
     * Scope para documentos de retención
     */
    public function scopeRetentions($query)
    {
        return $query->where('tax_is_retention', true);
    }

    /**
     * Scope para documentos por objeto de impuesto
     */
    public function scopeByTaxObject($query, $taxObject)
    {
        return $query->where('tax_object', $taxObject);
    }

    /**
     * Verificar si el documento tiene CFDI
     */
    public function hasCfdi(): bool
    {
        return !is_null($this->cfdi_uuid);
    }

    /**
     * Verificar si es una retención
     */
    public function isRetention(): bool
    {
        return $this->tax_is_retention;
    }

    /**
     * Calcular la base imponible si no está definida
     */
    public function calculateTaxBase(): float
    {
        if ($this->tax_base) {
            return $this->tax_base;
        }

        return $this->tax_is_retention
            ? $this->amount_paid
            : $this->amount_paid / (1 + $this->tax_rate);
    }

    /**
     * Get the invoice that owns the payment.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}