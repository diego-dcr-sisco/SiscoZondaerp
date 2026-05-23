<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPerception extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'payroll_id',
        'perception_type',
        'code',
        'description',
        'taxed_amount',
        'exempt_amount'
    ];

    protected $casts = [
        'taxed_amount' => 'decimal:2',
        'exempt_amount' => 'decimal:2',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}