<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class Lot extends Model
{
    use HasFactory;

    protected $table = 'lot';

    protected $fillable = [
        'id',
        'product_id',
        'warehouse_id',
        'registration_number',
        'expiration_date',
        'amount',
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
    ];

    // En el modelo Lot.php (asumiendo que estás desde el modelo Lot)
    public function countProducts()
    {
        $mp_query = MovementProduct::where('lot_id', $this->id);

        // Sumar amount para movimientos 1-4 (add_count)
        $add_count = $mp_query->clone()
            ->whereBetween('movement_id', [1, 4])
            ->sum('amount');

        // Sumar amount para movimientos 5-10 (less_count)
        $less_count = $mp_query->clone()
            ->whereBetween('movement_id', [5, 10])
            ->sum('amount');

        return $add_count - $less_count;
    }

    public function countProductsByWarehouse($warehouse_id)
    {
        $mp_query = MovementProduct::where('lot_id', $this->id)->where('warehouse_id', $warehouse_id);

        // Sumar amount para movimientos 1-4 (add_count)
        $add_count = $mp_query->clone()
            ->whereBetween('movement_id', [1, 4])
            ->sum('amount');

        // Sumar amount para movimientos 5-10 (less_count)
        $less_count = $mp_query->clone()
            ->whereBetween('movement_id', [5, 10])
            ->sum('amount');

        return $add_count - $less_count;
    }

    public function product()
    {
        return $this->belongsTo(ProductCatalog::class, 'product_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function movements()
    {
        return $this->hasManyThrough(
            MovementProduct::class,
            WarehouseProduct::class,
            'lot_id', // Foreign key on WarehouseProduct table
            'warehouse_product_id', // Foreign key on MovementProduct table
            'id', // Local key on Lot table
            'id' // Local key on WarehouseProduct table
        )->with('movement');
    }

    public function isExpired()
    {
        // If null or empty, consider it not expired (customize this as needed)
        if (empty($this->expiration_date)) {
            return false;
        }

        $expirationDate = $this->getAttribute('expiration_date');

        if (!$expirationDate instanceof Carbon) {
            $expirationDate = Carbon::parse($expirationDate);
        }

        return $expirationDate->isPast() || $expirationDate->isToday();
    }
}
