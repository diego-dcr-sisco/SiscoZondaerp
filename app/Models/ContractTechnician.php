<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractTechnician extends Model
{
    use HasFactory;

    protected $table = 'contract_technician';
    
    protected $fillable = [
        'contract_id',
        'technician_id',
    ];
}
