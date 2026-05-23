<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollOtherPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'other_payment_type',
        'code',
        'description',
        'amount',
        'employment_subsidy_amount'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'employment_subsidy_amount' => 'decimal:2',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}