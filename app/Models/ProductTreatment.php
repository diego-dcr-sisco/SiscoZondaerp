<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTreatment extends Model
{
    use HasFactory;

    protected $table = 'product_treatments';

    protected $fillable = [
        'product_id',
        'name',
        'description',
        'price',
    ];

    /**
     * Obtener el producto del catálogo al que pertenece este tratamiento.
     */
    public function productCatalog(): BelongsTo
    {
        return $this->belongsTo(ProductCatalog::class, 'product_id');
    }
}
