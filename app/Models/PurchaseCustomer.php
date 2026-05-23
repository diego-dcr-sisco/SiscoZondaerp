<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseCustomer extends Model
{
    use HasFactory;

    protected $table = "purchase_customers";

    protected $fillable = [
        'name',
        'address',
        'phone',
        'city',
        'rfc',
        'url',
    ];
}
