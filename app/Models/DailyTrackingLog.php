<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyTrackingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_tracking_id',
        'field',
        'old_value',
        'new_value',
        'changed_by',
    ];

    public function dailyTracking()
    {
        return $this->belongsTo(DailyTracking::class);
    }
}
