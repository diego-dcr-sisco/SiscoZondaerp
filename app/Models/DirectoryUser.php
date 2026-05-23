<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectoryUser extends Model
{
    use HasFactory;

    protected $table = 'directory_user';

    protected $fillable = [
        'id',
        'directory_id',
        'user_id',
    ];
}
