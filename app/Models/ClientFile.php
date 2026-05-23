<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientFile extends Model
{
    use HasFactory;

    protected $table = 'client_file';

    protected $fillable = [
        'id',
        'user_id',
        'directory_id',
        'name',
        'path'
    ];
}
