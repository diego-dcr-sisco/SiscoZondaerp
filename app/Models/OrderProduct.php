<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;

    protected $table = 'order_product';

    protected $fillable = [
        'order_id',
        'service_id',
        'product_id',
        'metric_id',
        'application_method_id',
        'lot_id',
        'possible_lot',
        'amount',
        'dosage',
    ];

    public function product()
    {
        return $this->belongsTo(ProductCatalog::class, 'product_id');
    }

    public function appMethod()
    {
        return $this->belongsTo(ApplicationMethod::class, 'application_method_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class, 'lot_id');
    }

    public function metric() {
        return $this->belongsTo(Metric::class, 'metric_id');
    }

    public function order() {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
