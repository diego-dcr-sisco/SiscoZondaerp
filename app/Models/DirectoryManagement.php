<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectoryManagement extends Model
{
    use HasFactory;

    protected $table = 'directory_management';

    protected $fillable = [
        'id',
        'user_id',
        'path',
        'is_visible',
        'created_at',
        'updated_at'
    ];
}
