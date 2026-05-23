<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerFile extends Model
{
    use HasFactory;

    protected $table = 'customer_file';

    protected $fillable = [
        'customer_id',
        'filename_id',
        'path',
        'expirated_at',
    ];

    public function filename() {
        return $this->belongsTo(Filenames::class, 'filename_id');
    }
}
