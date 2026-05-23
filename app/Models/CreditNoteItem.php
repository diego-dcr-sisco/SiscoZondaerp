<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNoteItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'credit_notes_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'credit_note_id',
        'quantity',
        'name',
        'product_code',
        'unit',
        'unit_code',
        'description',
        'identification_number',
        'unit_price',
        'subtotal',
        'discount_rate',
        'tax_name',
        'tax_rate',
        'tax_object',
        'tax_total',
        'tax_base',
        'tax_is_retention',
        'tax_is_federal_tax',
        'total',
    ];
}