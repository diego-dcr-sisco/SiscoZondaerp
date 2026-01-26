<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrative extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'administrative';
    protected $fillable = [
        "id",
        "user_id",
        "contract_type_id",
        "branch_id",
        "company_id",
        "birthdate",
        "phone",
        "company_phone",
        "address",
        "colony",
        "curp",
        "rfc",
        "nss",
        "city",
        "state",
        "country",
        "zip_code",
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
