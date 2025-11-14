<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderTechnician;
use App\Models\Technician;
use App\Traits\Loggable;

class Order extends Model
{
    use HasFactory, Loggable;

    protected $table = 'order';
    protected $fillable = [
        'id',
        'administrative_id',
        'customer_id',
        'status_id',
        'contract_id',
        'setting_id',
        'start_time',
        'end_time',
        'programmed_date',
        'completed_date',
        'status_id',
        'execution',
        'areas',
        'additional_comments',
        'customer_observations',
        'technical_observations',
        'recommendations',
        'comments',
        'customer_signature',
        'customer_sig_path',
        'signature_name',
        'price',
        'folio',
        'notes',
        'closed_by',
        'synchronized_by',
        'synchronized_at',
        'created_at',
        'updated_at'
    ];

    const STATUS_COMPLETED = 3; // Adjust this value based on your actual status codes
    const STATUS_APPROVED = 5;  // Adjust this value based on your actual status codes

    public function administrative()
    {
        return $this->belongsTo(Administrative::class, 'administrative_id');
    }

    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'status_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    // Definir la relaci贸n hasManyThrough con el modelo Technician
    public function technicians()
    {
        return $this->hasManyThrough(
            Technician::class,
            OrderTechnician::class,
            'order_id',
            'id',
            'id',
            'technician_id'
        );
    }

    public function techniciansScope()
    {
        return $this->hasMany(OrderTechnician::class);
    }

    public function hasTechnician($technicianId)
    {
        return $this->technicians->contains($technicianId);
    }

    public function getNameTechnicians()
    {
        $user_ids = $this->technicians()->pluck('user_id')->toArray();
        $technicians = User::whereIn('id', $user_ids)->get();
        return $technicians;
    }

    public function allTechnicians()
    {
        return $this->technicians->count() == Technician::count();
    }

    public function services()
    {
        return $this->hasManyThrough(
            Service::class,
            OrderService::class,
            'order_id',
            'id',
            'id',
            'service_id'
        );
    }

    public function orderServices()
    {
        return $this->hasMany(OrderService::class);
    }

    public function pests()
    {
        return $this->hasMany(OrderPest::class, 'order_id', 'id');
    }

    public function products()
    {
        return $this->hasMany(OrderProduct::class, 'order_id', 'id');
    }

    public function reportProducts()
    {
        return $this->hasManyThrough(
            ProductCatalog::class,
            OrderProduct::class,
            'order_id',
            'id',
            'id',
            'product_id'
        );
    }

    public function productsByService($serviceId)
    {
        return $this->products()->where('service_id', $serviceId)->get();
    }

    public function reportRecommendations()
    {
        return $this->hasMany(OrderRecommendation::class, 'order_id', 'id');
    }

    public function hasPest($serviceId, $pestId)
    {
        return $this->pests()->where('service_id', $serviceId)->where('pest_id', $pestId)->exists();
    }

    public function hasProduct($serviceId, $productId)
    {
        return $this->products()->where('service_id', $serviceId)->where('product_id', $productId)->exists();
    }

    public function hasAppMethod($serviceId, $productId, $applicationMethodId)
    {
        return $this->products()->where('service_id', $serviceId)->where('product_id', $productId)->where('application_method_id', $applicationMethodId)->exists();
    }

    public function hasArea($serviceId, $areaId)
    {
        return $this->areas()->where('service_id', $serviceId)->where('application_area_id', $areaId)->exists();
    }

    public function hasRecommendation($recommendationId)
    {
        return $this->reportRecommendations()->where('recommendation_id', $recommendationId)->exists();
    }

    public function hasAllTechnicians()
    {
        $technicians = Technician::pluck('id')->toArray();
        $orderTechs = OrderTechnician::where('order_id', $this->id)->pluck('technician_id')->toArray();
        if (array_diff($technicians, $orderTechs) || array_diff($orderTechs, $technicians)) {
            return false; // No son iguales
        } else {
            return true; // Son iguales
        }
    }

    public function findProduct($productId)
    {
        return ProductCatalog::find($productId);
    }


    public function findPest($pestId)
    {
        return $this->pests()->where('pest_id', $pestId)->first();
    }

    // public function incidents($deviceId=null)
    // {
    //     return $this->hasMany(OrderIncidents::class, 'order_id', 'id')->where('device_id', $deviceId);
    // }
    public function incidents()
    {
        return $this->hasMany(OrderIncidents::class, 'order_id', 'id');
    }

    public function incident($deviceId, $questionId)
    {
        return $this->hasOne(OrderIncidents::class, 'order_id', 'id')->where('device_id', $deviceId)->where('question_id', $questionId);
    }

    public function propagate()
    {
        return $this->hasMany(PropagateService::class, 'order_id', 'id');
    }

    public function propagateByService($serviceId)
    {
        return $this->propagate()->where('contract_id', $this->contract_id)->where('setting_id', $this->setting_id)->where('service_id', $serviceId)->first();
    }

    public function productsInDevices()
    {
        return $this->hasMany(DeviceProduct::class, 'order_id', 'id');
    }

    public function setting()
    {
        return $this->belongsTo(ContractService::class, 'setting_id', 'id');
    }

    public function closeUser()
    {
        return $this->belongsTo(User::class, 'closed_by', 'id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id', 'id');
    }

}
