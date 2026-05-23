<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerReference extends Model
{
    use HasFactory;

    protected $table = 'customer_reference';
    protected $fillable = [
        'id',
        'customer_id',
        'reference_type_id',
        'name',
        'phone',
        'email',
        'department',
        'address',
        'signature',
    ];

    public function referenceType() {
        return $this->belongsTo(Reference_type::class, 'reference_type_id');
    }
}
