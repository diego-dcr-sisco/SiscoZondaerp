<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkDepartment extends Model
{
    use HasFactory;

    protected $table = 'work_department';

    protected $fillable = [
        'name',
    ];
}
