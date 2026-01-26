<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseOrder extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'warehouse_order';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $fillable = [
        'id',
        'warehouse_id',
        'warehouse_movement_id',
        'movement_id',
        'order_id',
        'user_id',
        'product_id',
        'lot_id',
        'amount',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the warehouse associated with the warehouse movement order.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function warehouseMovement()
    {
        return $this->belongsTo(WarehouseMovement::class);
    }

    public function movement()
    {
        return $this->belongsTo(MovementType::class, 'movement_id');
    }

    /**
     * Get the order associated with the warehouse movement order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(ProductCatalog::class);
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }
}