<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'facturama_token',
        'folio',
        'serie', 
        'UUID', 

        'invoice_customer_id',
        'order_id',
        'contract_id',

        'issued_date',
        'due_date',
        'expedition_place',
        'tax', 
        'total', 
        'currency',
        'notes', 
        'status', 
        'payment_form',
        'payment_method',

        'receiver_name',
        'receiver_rfc',
        'receiver_cfdi_use',
        'receiver_fiscal_regime',
        'receiver_tax_zip_code',

        'stamped_date',
        'cfdi_usage',
        'cfdi_sign',
        'sat_cert_number',
        'sat_sign',
        'rfc_prov_cert',
        
        'csd_serial_number',
        
        'xml_file',
        'pdf_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issued_date' => 'date',
        'due_date' => 'date',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'status' => 'integer',
        'payment_form' => 'string',
        'payment_method' => 'string',
    ];

    /**
     * Get the customer associated with the invoice.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(InvoiceCustomer::class, 'invoice_customer_id');
    }

    /**
     * Get the order associated with the invoice.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the contract associated with the invoice.
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Get the items for the invoice.
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Scope a query to only include pending invoices.
     */
    public function scopePending($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope a query to only include paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to only include cancelled invoices.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 2);
    }

    /**
     * Get the formatted folio with serie.
     */
    public function getFullFolioAttribute(): string
    {
        return $this->serie . $this->folio;
    }

    public function isPending(): bool
    {
        return $this->status === 0;
    }

    public function isPaid(): bool
    {
        return $this->status === 1;
    }

    public function isCancelled(): bool
    {
        return $this->status === 2;
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total, 2);
    }

    public function getFormattedTaxAttribute(): string
    {
        return '$' . number_format($this->tax, 2);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->total - $this->tax;
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal, 2);
    }

    public function getStatus(): string
    {
        return match ($this->status) {
            0 => 'Pendiente',
            1 => 'Pagada',
            2 => 'Timbrada',
            3 => 'Cancelada',
            default => 'Desconocido',
        };
    }

    public function paymentMethod(): string
    {
        return match ($this->payment_method) {
            1 => 'PUE: Pago en una sola exhibición',
            2 => 'PPD: Pago en parcialidades o diferido',
            default => 'Desconocido',
        };
    }

    public function paymentForm(): string
    {
        $forms = $this->getPaymentFormOptions();
        return $forms[$this->payment_form] ?? 'Desconocido';
    }

    public static function getPaymentFormOptions(): array
    {
        return [
            '01' => 'Efectivo',
            '02' => 'Cheque nominativo',
            '03' => 'Transferencia electrónica de fondos',
            '04' => 'Tarjeta de crédito',
            '05' => 'Monedero electrónico',
            '06' => 'Dinero electrónico',
            '08' => 'Vales de despensa',
            '12' => 'Dación en pago',
            '13' => 'Pago por subrogación',
            '14' => 'Pago por consignación',
            '15' => 'Condonación',
            '17' => 'Compensación',
            '23' => 'Novación',
            '24' => 'Confusión',
            '25' => 'Remisión de deuda',
            '26' => 'Prescripción o caducidad',
            '27' => 'A satisfacción del acreedor',
            '28' => 'Tarjeta de débito',
            '29' => 'Tarjeta de servicios',
            '30' => 'Aplicación de anticipos',
            '31' => 'Intermediarios',
            '99' => 'Por definir',
        ];
    }

    public function getPaymentMethodOptions(): array
    {
        return [
            1 => 'PUE: Pago en una sola exhibición',
            2 => 'PPD: Pago en parcialidades o diferido',
        ];
    }

    public function creditNotes() {
        return $this->hasMany(CreditNote::class);
    }

}