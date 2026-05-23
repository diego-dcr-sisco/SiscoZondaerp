<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderName extends Model
{
    use HasFactory;
    protected $table = 'ordername';

    protected $fillable = [
        'order_id',
        'nameReport',
    ];
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function getClientIdAttribute()
    {
        return $this->order->customer_id ?? null;
    }
}
