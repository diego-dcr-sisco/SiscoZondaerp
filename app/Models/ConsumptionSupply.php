<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumptionSupply extends Model
{
    use HasFactory;

    protected $table = 'consumption_supplies';

    protected $fillable = [
        'consumption_id',
        'is_supplied',
        'supplied_amount',
        'supply_notes',
        'supplied_by',
        'supplied_at'
    ];

    protected $casts = [
        'is_supplied' => 'boolean',
        'supplied_amount' => 'decimal:2',
        'supplied_at' => 'datetime'
    ];

    /**
     * Relación con el modelo Consumption
     */
    public function consumption()
    {
        return $this->belongsTo(Consumption::class);
    }

    /**
     * Relación con el usuario que surtió
     */
    public function suppliedBy()
    {
        return $this->belongsTo(User::class, 'supplied_by');
    }

    /**
     * Scope para productos surtidos
     */
    public function scopeSupplied($query)
    {
        return $query->where('is_supplied', true);
    }

    /**
     * Scope para productos no surtidos
     */
    public function scopeNotSupplied($query)
    {
        return $query->where('is_supplied', false);
    }
} 