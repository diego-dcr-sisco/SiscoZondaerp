<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisition extends Model
{
    use HasFactory;
    protected $table = 'purchase_requisitions';

    protected $fillable = [
        'user_id',
        'customer_id',
        'folio',
        'status',
        'request_date',
        'observations',
        'approval_at',
    ];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function products()
    {
        return $this->hasMany(ProductRequisition::class);
    }

    public function getFolioIdAttribute($value)
    {
        return strtoupper($value);
    }

    public function setFolioIdAttribute($value)
    {
        $this->attributes['folio_id'] = strtoupper($value);
    }

    public function getRequiredByDateAttribute($value)
    {
        return date('d/m/Y', strtotime($value));
    }

    public function setRequiredByDateAttribute($value)
    {
        $this->attributes['request_date'] = date('Y-m-d', strtotime($value));
    }

    public function getApprovalDateAttribute($value)
    {
        return date('d/m/Y', strtotime($value));
    }

    public function setApprovalDateAttribute($value)
    {
        $this->attributes['approval_at'] = date('Y-m-d', strtotime($value));
    }
}
