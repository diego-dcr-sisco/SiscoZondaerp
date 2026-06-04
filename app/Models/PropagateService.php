<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropagateService extends Model
{
    use HasFactory;

    protected $table = 'propagate_service';

    protected $fillable = [
        'id',
        'order_id',
        'service_id',
        'contract_id',
        'setting_id',
        'text',
    ];

    // En app/Models/PropagateService.php
    public function contractService()
    {
        return $this->belongsTo(ContractService::class, 'setting_id');
    }
}
