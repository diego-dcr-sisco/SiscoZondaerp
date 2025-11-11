<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FloorplanArea extends Model
{
    use HasFactory;
    protected $table = 'floorplan_areas';

    protected $fillable = [
        'floorplan_id',
        'application_area_id'
    ];

    public function applicationArea()
    {
        return $this->belongsTo(ApplicationArea::class, 'application_area_id');
    }
}
