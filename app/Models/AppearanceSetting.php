<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppearanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'primary_color',
        'secondary_color',
        'logo_path',
        'watermark_path',
        'watermark_opacity'
    ];

    protected $attributes = [
        'primary_color' => '#64b5f6',
        'secondary_color' => '#b0bec5',
        'logo_path' => 'images/logo_reporte.png',
        'watermark_path' => 'images/watermark.png',
        'watermark_opacity' => 0.1
    ];
}
