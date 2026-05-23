<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovementProduct extends Model
{
    use HasFactory;

    protected $table = 'movement_products';

    protected $fillable = [
        'id',
        'warehouse_movement_id',
        'movement_id',
        'warehouse_id',
        'product_id',
        'lot_id',
        'amount',
        'created_at',
        'updated_at'
    ];

    public function warehouseMovement()
    {
        return $this->belongsTo(WarehouseMovement::class, 'warehouse_movement_id');
    }

    public function movement()
    {
        return $this->belongsTo(MovementType::class, 'movement_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductCatalog::class, 'product_id');
    }

    public function lot()
    {
        return $this->belongsTo(Lot::class);
    }

    public static function getProductsGroupedByLot($warehouseId)
    {
        return self::where('warehouse_id', $warehouseId)
            ->selectRaw('
            lot_id,
            product_id,
            SUM(CASE WHEN movement_id BETWEEN 1 AND 4 THEN amount ELSE 0 END) as add_amount,
            SUM(CASE WHEN movement_id BETWEEN 5 AND 10 THEN amount ELSE 0 END) as less_amount
        ')
            ->with(['product', 'lot'])
            ->groupBy('lot_id', 'product_id')
            ->get()
            ->mapToGroups(function ($item) {
                return [
                    $item->lot_id => [
                        'lot' => $item->lot, // Incluimos el lot_id en los datos
                        'product' => $item->product,
                        'amount' => [
                            'add' => (float) $item->add_amount,
                            'less' => (float) $item->less_amount,
                            'net' => (float) ($item->add_amount - $item->less_amount)
                        ]
                    ]
                ];
            })
            ->toArray();
    }
}

