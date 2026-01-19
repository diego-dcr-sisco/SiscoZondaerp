<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    protected $table = 'tracking';

    protected $fillable = [
        'trackable_id', 
        'trackable_type',
        'service_id',
        'customer_id',
        'order_id',
        'next_date',
        'range',
        'title',
        'description',
        'status'
    ];

    public function trackable()
    {
        return $this->morphTo();
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}