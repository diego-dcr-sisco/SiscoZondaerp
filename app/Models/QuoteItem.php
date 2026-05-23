<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    protected $table = 'quote_items';

    protected $casts = [
        'qty' => 'float',
        'unit_price' => 'float',
        'line_total' => 'float',
    ];

    protected $fillable = [
        'quote_id',
        'position',
        'name',
        'description',
        'qty',
        'unit',
        'unit_price',
        'line_total',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}