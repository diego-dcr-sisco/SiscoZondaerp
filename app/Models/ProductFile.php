<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFile extends Model
{
    use HasFactory;

    protected $table = 'product_file';

    protected $fillable = [
        'product_id',
        'filename_id',
        'path',
        'expirated_at',
    ];

    public function product() {
        return $this->belongsTo(ProductCatalog::class ,'product_id');
    }

    public function filename() {
        return $this->belongsTo(Filenames::class, 'filename_id');
    }
}
