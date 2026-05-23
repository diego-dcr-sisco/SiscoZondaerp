<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $table = 'suppliers';
    protected  $fillable = [
        'category_id',
        'name',
        'rfc',
        'address',
        'phone',
        'email'
    ];

    public function category()
    {
        return $this->belongsTo(SupplierCategory::class, 'category_id');
    }
}

