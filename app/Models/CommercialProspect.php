<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommercialProspect extends Model
{
    use HasFactory;

    protected $table = 'commercial_prospects';

    protected $fillable = [
        'commercial_name',
        'date',
        'commerce_type',
        'quotation_status',
        'close_reason',
        'contact_method',
        'scheduled_date',
    ];

    protected $casts = [
        'date' => 'date',
        'scheduled_date' => 'date',
    ];
}
