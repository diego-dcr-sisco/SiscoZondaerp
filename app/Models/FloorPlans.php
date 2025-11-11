<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;


class FloorPlans extends Model
{
    use HasFactory;

    protected $table = 'floorplans';

    protected $fillable = [
        'id',
        'customer_id',
        'service_id',
        'filename',
        'path'
    ];


    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function versions()
    {
        return $this->hasMany(FloorplanVersion::class, 'floorplan_id', 'id')->orderBy('version', 'desc');
    }

    public function version()
    {
        $floorplan_version = $this->versions()->first();
        return $floorplan_version ? $floorplan_version->version : null;
    }

    public function versionByDate($date)
    {
        $parsedDate = Carbon::parse($date)->toDateString();

        $version = $this->versions()
            ->whereDate('updated_at', '<=', $parsedDate)
            ->latest('updated_at') // Orden DESC explícito
            ->first();

        return $version?->version ?? $this->versions()->oldest('updated_at')->first()?->version;
    }

    public function lastVersion() {
        return $this->versions()->latest()->first()->version ?? null;
    }

    public function devices($version)
    {
        return $this->hasMany(Device::class, 'floorplan_id', 'id')->where('version', $version)->orderBy('nplan', 'asc');
    }
}
