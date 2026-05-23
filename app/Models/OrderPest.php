<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPest extends Model
{
    use HasFactory;
    protected $table = 'order_pest';

    protected $fillable = [
        'id',
        'order_id',
        'service_id',
        'pest_id',
        'total',
    ];

    public function pest() {
        return $this->belongsTo(PestCatalog::class, 'pest_id');
    }
}
