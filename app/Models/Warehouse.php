<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductCatalog;


class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'warehouse';

    protected $fillable = [
        'id',
        'branch_id',
        'technician_id',
        'name',
        'allow_material_receipts',
        'is_active',
        'is_matrix',
        'observations'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function movements()
    {
        return $this->hasMany(WarehouseMovement::class, 'destination_warehouse_id', 'id');
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class, 'technician_id');
    }

    public function products()
    {
        // Relación a productos a través de la tabla de movimientos (movement_products)
        return $this->belongsToMany(ProductCatalog::class, 'movement_products', 'warehouse_id', 'product_id')
            ->withPivot('lot_id', 'amount', 'movement_id')
            ->distinct();
    }
}
