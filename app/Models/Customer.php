<?php

namespace App\Models;

use Faker\Core\File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\Permission;

class Customer extends Model
{
    use HasFactory;

    private $contact_medium_values = [
        'whatsapp' => 'WhatsApp',
        'sms' => 'Mensaje SMS',
        'call' => 'Llamada telefónica',
        'email' => 'Correo electrónico',
        'flyer' => 'Volanteo físico'
    ];

    protected $table = 'customer';
    protected $fillable = [
        'id',
        'customer_file_id',
        'company_category_id',
        'administrative_id',
        'company_id',
        'service_type_id',
        'branch_id',
        'contact_medium',
        'tax_regime_id',
        'status',
        'starting_time',
        'final_schedule',
        'commercial_zone',
        'meters',
        'm2',
        'm3',
        'blueprints',
        'name',
        'code',
        'print_doc',
        'validate_certificate',
        'businessname',
        'address',
        'tax_name',
        'rfc',
        'zip_code',
        'city',
        'state',
        'tel',
        'email',
        'map_location_url',
        'general_sedes',
        'end_time',
        'start_time',
        'phone',
        'reason',
        'tracking_at',

        'url',
        'portal_email',
        'username',
        'password',

        'created_at',
        'updated_at'
    ];

    private $size = 50;

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'customer_id', 'id');
    }

    public function services()
    {
        return $this->hasManyThrough(
            Service::class,
            ContractService::class,
            'contract_id',
            'id',
            'id',
            'service_id'
        );
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function companyCategory()
    {
        return $this->belongsTo(CompanyCategory::class, 'company_category_id');
    }

    public function taxRegime()
    {
        return $this->belongsTo(TaxRegime::class, 'tax_regime_id');
    }

    public function matrix()
    {
        return $this->belongsTo(Customer::class, 'general_sedes');
    }

    public function references()
    {
        return $this->hasMany(CustomerReference::class, 'customer_id', 'id');
    }

    public function files()
    {
        return $this->hasMany(CustomerFile::class, 'customer_id', 'id');
    }

    public function floorplans()
    {
        return $this->hasMany(FloorPlans::class, 'customer_id', 'id');
    }

    public function applicationAreas()
    {
        return $this->hasMany(ApplicationArea::class, 'customer_id', 'id');
    }

    public function sedes()
    {
        return $this->hasMany(Customer::class, 'general_sedes', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id', 'id');
    }

    public function ordersByStatus($statusId)
    {
        return $this->orders()
            ->where('status_id', $statusId)->orderByDesc('completed_date');
    }

    public function ordersPlaced()
    {
        return $this->orders()
            ->whereIn('status_id', [3, 4, 5])->orderByDesc('completed_date');
        //->whereNotNull('completed_date');
    }

    /*public function ordersPending()
    {
        return $this->orders()
            ->where('status_id', 1);
        //->whereNotNull('completed_date');
    }*/

    public function countOrdersbyStatus($statusId) {
        return $this->orders()->where('status_id', $statusId)->count();
    }

    public function ordersPaginate() {
        return $this->orders()->orderBy('status_id', 'asc')->paginate($this->size);
    }

    public function properties()
    {
        return $this->hasManyThrough(
            Properties::class,
            CustomerProperties::class,
            'customer_id',
            'id',
            'id',
            'property_id'
        );
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function administrative()
    {
        return $this->belongsTo(User::class, 'administrative_id', 'id');
    }

    public function trackings()
    {
        return $this->morphMany(Tracking::class, 'trackable');
    }

    public function contactMedium() {
        return $this->contact_medium_values[$this->contact_medium] ?? '-';
    }

    public function taxData()
    {
        return $this->hasOne(InvoiceCustomer::class, 'customer_id', 'id');
    }

    public function creditLine()
    {
        return $this->hasOne(CreditLine::class, 'customer_id', 'id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'customer_id', 'id');
    }

    /**
     * Relación con zonas a través de la tabla customer_zones
     */
    public function zones()
    {
        return $this->belongsToMany(Zone::class, 'customer_zones', 'customer_id', 'zone_id')
                    ->withPivot('status', 'observation', 'file')
                    ->withTimestamps();
    }

    /**
     * Relación directa con customer_zones
     */
    public function customerZones()
    {
        return $this->hasMany(CustomerZone::class, 'customer_id');
    }

    /**
     * Relación con zonas activas solamente
     */
    public function activeZones()
    {
        return $this->belongsToMany(Zone::class, 'customer_zones', 'customer_id', 'zone_id')
                    ->wherePivot('status', 'active')
                    ->withPivot('status', 'observation', 'file')
                    ->withTimestamps();
    }

}
