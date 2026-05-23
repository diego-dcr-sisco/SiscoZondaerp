<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Branch extends Model
{
    use HasFactory;
    protected $table = 'branch';
   
    protected $fillable = [
        'id',
        'status_id',
        'name',
        'code',
        'fiscal_name',
        'email',
        'alt_email',
        'phone',
        'alt_phone',
        'address',
        'colony',
        'zip_code',
        'city',
        'state',
        'country',
        'license_number', // Corregido el nombre de la columna
        'rfc', 
        'fiscal_regime', 
        'url',
        'description', // También corrige un typo aquí si "description" es el nombre correcto
    ];

    public function status() {
        return $this->belongsTo(Status::class, 'status_id');
    }

}
