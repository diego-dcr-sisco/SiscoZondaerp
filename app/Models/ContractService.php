<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractService extends Model
{
    use HasFactory;

    protected $table = 'contract_service';

    protected $fillable = [
        'id',
        'contract_id',
        'service_id',
        'execution_frequency_id',
        'interval',
        'days',
        'total',
        'service_description',
    ];

    public function service() {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function execfrequency() {
        return $this->belongsTo(ExecFrequency::class, 'execution_frequency_id');
    }
}
