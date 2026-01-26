<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'service';

    protected $fillable = [
        'id',
        'prefix',
        'service_type_id',
        'business_line_id',
        'status_id',
        'name',
        'description',
        'time',
        'time_unit',
        'cost',
        'has_pests',
        'has_application_methods',
    ];

    public function prefixType() {
        return $this->belongsTo(ServicePrefix::class,'prefix');
    }

    public function businessLine()
    {
        return $this->belongsTo(LineBusiness::class, 'business_line_id');
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function appMethods()
    {
        return $this->hasManyThrough(
            ApplicationMethod::class,
            ApplicationMethodService::class,
            'service_id',
            'id',
            'id',
            'application_method_id'
        );
    }

    public function pests()
    {
        return $this->hasManyThrough(
            PestCatalog::class,
            PestService::class,
            'service_id',
            'id',
            'id',
            'pest_id'
        );
    }

    public function propagateByContract($contractId) {
        return $this->hasMany(PropagateService::class,'service_id')->where('contract_id', $contractId)->get();
    }

    public function hasPest($pestID)
    {
        return $this->pests->contains('id', $pestID);
    }

    public function hasAppMethod($appMethodID)
    {
        return $this->appMethods->contains('id', $appMethodID);
    }

    public function products($op)
    {
        $pestIds = $this->pests()->pluck('pest_catalog.id');
        $methodIds = $this->appMethods()->pluck('application_method_id');
        
        $product_pestIds = ProductPest::whereIn('pest_id', $pestIds)->pluck('product_id')->toArray();
        $product_methodIds = Dosage::whereIn('methd_id', $methodIds)->pluck('prod_id')->toArray();

        $productIds = array_intersect($product_pestIds, $product_methodIds);
        $products = ProductCatalog::whereIn('id', $productIds);

        return $op == 0 ? $products : $products->get();
    }

    public function floorplans()
    {
        return $this->hasMany(FloorPlans::class, 'service_id', 'id');
    }

    public function hasDevices() {
        $floorplanIds = $this->floorplans()->pluck('id')->toArray();
        $devices = Device::whereIn('floorplan_id', $floorplanIds)->get();
        return count($devices) > 0;
    }
}
