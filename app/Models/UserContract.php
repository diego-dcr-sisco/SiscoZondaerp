<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserContract extends Model
{
    use HasFactory;

    protected $table = 'user_contract';

    protected $fillable = [
        'user_id',
        'contract_type_id',
        'contract_startdate',
        'contract_enddate',
    ];

    public function type() {
        return $this->belongsTo(ContractType::class, 'contract_type_id');
    }
}
