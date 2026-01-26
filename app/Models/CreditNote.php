<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNote extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'invoice_id',
        'facturama_token',
        'UUID',
        'folio',
        'serie',
        'expedition_place',
        'status',
        'payment_form',
        'payment_method',
        'type',
        'cfdi_uuid',
        'receiver_name',
        'receiver_rfc',
        'receiver_cfdi_use',
        'receiver_fiscal_regime',
        'receiver_tax_zip_code',
        'stamped_at',
        'created_at',
        'updated_at'
    ];

    protected $status_options = [
        'paid' => 'Pagada',
        '1' => 'Pendiente',
        '2' => 'Vencida',
        '3' => 'Cancelada',
        '4' => 'Vigente',
        '5' => 'Timbrada',
        '6' => 'Parcial',
        '7' => 'En Proceso',
        '8' => 'Enviada',
        '9' => 'Rechazada'
    ];

    public function items()
    {
        return $this->hasMany(CreditNoteItem::class);
    }

    public function getFormattedTotalAttribute()
    {
        return $this->items()->sum('total');
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

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}