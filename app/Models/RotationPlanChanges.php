<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RotationPlanChanges extends Model
{
    use HasFactory;

    protected $table = 'rotation_plan_changes';

    protected $fillable = [
        'rotation_plan_id',
        'no_review',
        'description'
    ];

    public function rotationPlan()
    {
        return $this->belongsTo(RotationPlan::class);
    }
}