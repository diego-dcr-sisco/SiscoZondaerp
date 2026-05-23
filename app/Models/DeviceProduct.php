<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceProduct extends Model
{
    use HasFactory;

    protected $table = 'device_product';

    protected $fillable = [
        'order_id',
        'device_id',
        'product_id',
        'application_method_id',
        'lot_id',
        'quantity',
        'possible_lot',
    ];
    
    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductCatalog::class, 'product_id');
    }

    public function applicationMethod()
    {
        return $this->belongsTo(ApplicationMethod::class, 'application_method_id');
    }

    // Scopes
    public function scopeByOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeByDevice($query, $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    // Accessors & Mutators
    public function getTotalPriceAttribute($value)
    {
        // Calculate total price if not set
        if (!$value && $this->quantity && $this->unit_price) {
            return $this->quantity * $this->unit_price;
        }
        return $value;
    }

    public function setTotalPriceAttribute($value)
    {
        // Auto-calculate total price when quantity or unit_price changes
        if (!$value && $this->quantity && $this->unit_price) {
            $this->attributes['total_price'] = $this->quantity * $this->unit_price;
        } else {
            $this->attributes['total_price'] = $value;
        }
    }

    // Helper methods
    public function calculateTotalPrice()
    {
        return $this->quantity * $this->unit_price;
    }

    public function getFormattedTotalPrice()
    {
        return number_format($this->total_price, 2);
    }

    public function getFormattedQuantity()
    {
        return number_format($this->quantity, 2);
    }

    // Validation rules
    public static function validationRules()
    {
        return [
            'order_id' => 'required|exists:order,id',
            'device_id' => 'required|exists:device,id',
            'product_id' => 'required|exists:product_catalog,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'application_method_id' => 'nullable|exists:application_method,id',
            'concentration' => 'nullable|numeric|min:0',
            'dilution_ratio' => 'nullable|string|max:50',
            'application_area' => 'nullable|numeric|min:0',
            'observations' => 'nullable|string|max:1000'
        ];
    }
}
