<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderFrequency extends Model
{
    use HasFactory;

    protected $table = 'order_frequency';
    
    protected $fillable = [
        'id', 
        'order_id',
        'number',
        'frequency',
        'next_date',
    ];

    public function order() {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
