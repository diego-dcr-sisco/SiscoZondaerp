<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Enums\TaxpayerType;

class InvoiceCustomer extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'taxpayer',
        'type',
        'name',
        'social_reason',
        'rfc',
        'phone',
        'email',
        'curp',
        'nss',
        'salary_daily',
        'position_risk',
        'department',
        'position',
        'tax_system',
        'cfdi_usage',
        'zip_code',
        'state',
        'city',
        'address',
        'credit_limit',
        'credit_days',
        'payment_method',
        'payment_form',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    /*protected $casts = [
        'salary_daily' => 'decimal:2',
        'credit_limit' => 'float',
        'credit_days' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];*/

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'payment_method' => 'PUE',
        'payment_form' => '01',
        'status' => 'no_facturable',
    ];

    public function getTypeNameAttribute(): string
    {
        $type = $this->getRawOriginal('type');

        return match ($type) {
            'worker' => 'Trabajador',
            'client' => 'Cliente',
            default => 'Tipo no definido'
        };
    }

    public function getTaxpayerNameAttribute(): string
    {
        $originalTaxpayer = $this->getRawOriginal('taxpayer');
        return TaxpayerType::from($originalTaxpayer)->name();
    }

    public function getStatusTextColorAttribute(): array
    {
        return match ($this->status) {
            'facturable' => ['color' => 'text-success', 'text' => 'Facturable'],
            'moroso' => ['color' => 'text-danger', 'text' => 'Moroso'],
            'no_facturable' => ['color' => 'text-secondary', 'text' => 'No Facturable']
        };
    }
}
