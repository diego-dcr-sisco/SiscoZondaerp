<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RotationPlanProduct extends Model
{
    use HasFactory;

    protected $table = 'rotation_plan_products';

    protected $fillable = [
        'id',
        'rotation_plan_id',
        'product_id',
        'color',
        'months',
        'created_at',
        'updated_at'
    ];

    public function product() {
        return $this->belongsTo(ProductCatalog::class, 'product_id');
    }
}
