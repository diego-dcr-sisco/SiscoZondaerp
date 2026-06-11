<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTreatment extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo en la base de datos de SiscoZonda.
     *
     * @var string
     */
    protected $table = 'product_treatments';

    /**
     * Los atributos que son asignables masivamente (Mass Assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'name',
        'description',
        'price',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function productCatalog(): BelongsTo
    {
        return $this->belongsTo(ProductCatalog::class, 'product_id');
    }
}
