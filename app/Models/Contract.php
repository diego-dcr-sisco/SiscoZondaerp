<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $table = 'contract';

    protected $fillable = [
        'id',
        'customer_id',
        'user_id',  
        'startdate',
        'enddate',
        'status',
        'file',
    ];

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'contract_id');
    }

    public function services()
    {
        return $this->hasMany(ContractService::class, 'contract_id');
    }

    public function technicians()
    {
        return $this->hasManyThrough(
            Technician::class,
            ContractTechnician::class,
            'contract_id',
            'id',
            'id',
            'technician_id'
        );
    }

    public function hasTechnician($technicianId)
    {
        return $this->technicians()->where('technician.id', $technicianId)->exists();
    }

    public function technicianNames() {
        $technicianIds = $this->technicians()->pluck('user_id');
        $users = User::whereIn('id', $technicianIds)->get()->pluck('name')->toArray();
        return $users;
    }

    public function rotationPlans() {
        return $this->hasMany(RotationPlan::class, 'contract_id', 'id');
    }

    public function hasRotationPlan() {
        return $this->rotationPlans()->count() > 0;
    }

    public function rotationPlan() {
        return $this->rotationPlans()->latest()->first();    
    }    

    public function invoice() {
        return $this->hasOne(Invoice::class, 'contract_id', 'id');
    }
}
