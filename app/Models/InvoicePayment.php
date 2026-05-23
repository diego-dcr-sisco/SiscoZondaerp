<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\carbon;

class InvoicePayment extends Model
{
    use HasFactory;
    protected $fillable = [
        'invoice_id',
        'customer_id',
        'user_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
