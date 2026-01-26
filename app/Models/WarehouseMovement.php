<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseMovement extends Model
{
    use HasFactory;

    protected $table = 'warehouse_movements';

    protected $fillable = [
        // modificado el 7 de julio de 2025, se quita el json de products
        // Los productos se gestionan en la tabla movement_products        
        'id',
        'warehouse_id',
        'destination_warehouse_id',
        'movement_id',
        'user_id',
        'date',
        'time',
        'warehouse_signature', // *
        'technician_signature', // *
        'image_path', // *
        'observations',
        'is_active'
    ];

    public function movement()
    {
        return $this->belongsTo(MovementType::class, 'movement_id');
    }
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function destinationWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function linkedToTechnician()
    {
        if ($this->destination_warehouse_id) {
            $warehouse = Warehouse::find($this->destination_warehouse_id);
            return $warehouse->technician_id;
        }
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }


    public function products()
    {
        return $this->hasMany(MovementProduct::class, 'warehouse_movement_id', 'id');
    }

    public function warehouseType()
    {
        if ($this->warehouse_id) {
            return 1;
        }

        if ($this->destination_warehouse_id) {
            return 2;
        }

        return null;
    }

    public function warehouseProducts($warehouseId)
    {
        return $this->products()->where('warehouse_id', $warehouseId)->get();
    }

    public function hasWarehouseProducts($warehouseId)
    {
        return $this->products()
            ->where('warehouse_id', $warehouseId)
            ->exists();
    }

    public function triggering()
    {

    }

    //esta relación es la que usa para filtro por productos

    public function movementProducts()
{
    return $this->hasMany(MovementProduct::class, 'movement_id');
}

}