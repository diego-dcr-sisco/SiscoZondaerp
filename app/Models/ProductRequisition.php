<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRequisition extends Model
{
    use HasFactory;
    protected $table = 'product_requisitions';

    protected $fillable = [
        'code',
        'type',
        'purchase_requisition_id',
        'quantity',
        'unit',
        'description',
        'supplier1_id',
        'supplier1_cost',
        'supplier2_id',
        'supplier2_cost',
        'approved_supplier_id',
        'purchase_value',
        'commercial_value'
    ];

    public $timestamps = true;

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function supplier1()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function supplier2()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function approvedSupplier()
    {
        return $this->belongsTo(Supplier::class);
    }


}
