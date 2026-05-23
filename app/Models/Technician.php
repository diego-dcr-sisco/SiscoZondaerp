<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Technician extends Model
{
    use HasFactory;
    protected $table = 'technician';

    protected $fillable = [
        "id",
        "user_id",
        "contract_type_id",
        "branch_id",
        "company_id",
        "curp",
        "rfc",
        "nss",
        "phone",
        "company_phone",
        "address",
        "colony",
        "city",
        "state",
        "country",
        "zip_code",
        "birthdate",
        "hiredate",
        "salary",
        "clabe",
        "signature",
        "created_at",
        "updated_at"
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function company() {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch() {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function contractType() {
        return $this->belongsTo(ContractType::class, 'contract_type_id');
    }
}
