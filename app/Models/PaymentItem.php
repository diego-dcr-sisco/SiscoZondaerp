<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payments_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'payment_id',
        'payment_form',
        'payment_date',
        'amount',
        'currency',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    /*protected $casts = [
        'payment_form' => 'integer',
        'amount' => 'decimal:2', // Si el amount representa dinero
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];*/

    /**
     * Get the payment that owns the payment item.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Accessor para formatear el amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ' . $this->currency;
    }

    /**
     * Scope para filtrar por forma de pago
     */
    public function scopeByPaymentForm($query, $paymentForm)
    {
        return $query->where('payment_form', $paymentForm);
    }

    /**
     * Scope para filtrar por moneda
     */
    public function scopeByCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    public function relatedDocuments()
    {
        return $this->hasMany(PaymentsRelatedDocument::class);
    }
}

