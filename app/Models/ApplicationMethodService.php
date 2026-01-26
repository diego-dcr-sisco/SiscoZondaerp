<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationMethodService extends Model
{
    use HasFactory;

    protected $table = 'application_method_service';

    protected $fillable = [
        'application_method_id',
        'service_id',
    ];
}
