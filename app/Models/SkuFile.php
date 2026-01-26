<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkuFile extends Model
{
    use HasFactory;

    protected $table = 'sku_file';

    protected $fillable = [
        'sku_code',
        'file_type',
    ];
}
