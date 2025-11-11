<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'invoice_id',
        'concept_id',
        'quantity',
        'name',
        'product_code',
        'unit_code',
        'unit',
        'description',
        'identification_number',
        'unit_price',
        'subtotal',
        'discount_rate',
        'tax_object',
        'tax_name',
        'tax_rate',
        'tax_total',
        'tax_base',
        'tax_is_retention',
        'tax_is_federal_tax',
        'total'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    /*protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'float',
        'subtotal' => 'float',
        'tax_total' => 'float',
        'tax_base' => 'float',
        'tax_is_retention' => 'boolean',
        'tax_is_federal_tax' => 'boolean',
        'total' => 'string',
    ];*/
}