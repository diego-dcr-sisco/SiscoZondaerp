<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EconomicDataProduct extends Model
{
    use HasFactory;
    protected $table = 'economic_data_product';
    protected $fillable = [
        'purchase_price',
        'min_purchase_unit',
        'mult_purchase',
        'supplier_id',
        'selling',//true o false
        'selling_price',
        'subaccount_purchases',
        'subaccount_sales'
    ];

    public function supplier() {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
