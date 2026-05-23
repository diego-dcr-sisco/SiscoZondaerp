<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectoryPermission extends Model
{
    use HasFactory;

    protected $table = 'directory_permissions';

    protected $fillable = [
        'id',
        'user_id',
        'path',
        'created_at',
        'updated_at'
    ];
}
