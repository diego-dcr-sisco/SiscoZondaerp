<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract_File extends Model
{
    use HasFactory;
    protected $table = 'contract_file';

    protected $fillable = [
        'id',
        'contract_id',
        'path'
    ];
}
