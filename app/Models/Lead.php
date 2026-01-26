<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    private $contact_medium_values = [
        'whatsapp' => 'WhatsApp',
        'sms' => 'Mensaje SMS',
        'call' => 'Llamada telefónica',
        'email' => 'Correo electrónico',
        'flyer' => 'Volanteo físico'
    ];

    protected $table = 'lead';

    protected $fillable = [
        'id',
        'company_category_id',
        'administrative_id',
        'service_type_id',
        'branch_id',
        'company_id',
        'contact_medium',
        'name',
        'address',
        'state',
        'city',
        'status',
        'zip_code',
        'phone',
        'email',
        'map_location_url',
        'reason',
        'tracking_at',
        'created_at',
        'updated_at',
        'converted_customer_id'
    ];

    /**
     * Check if the lead was converted to a customer
     */
    public function wasConvertedToCustomer(): bool
    {
        return !is_null($this->converted_customer_id);
    }

    /**
     * Get the customer this lead was converted to
     */
    public function convertedCustomer()
    {
        return $this->belongsTo(Customer::class, 'converted_customer_id');
    }

    public function serviceType() {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function companyCategory()
    {
        return $this->belongsTo(CompanyCategory::class, 'company_category_id');
    }

    public function trackings()
    {
        return $this->morphMany(Tracking::class, 'trackable');
    }

    public function contactMedium() {
        return $this->contact_medium_values[$this->contact_medium] ?? '-';
    }
}
