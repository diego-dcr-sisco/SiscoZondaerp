<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class UserFile extends Model
{
    use HasFactory;

    protected $table = 'user_file';

    protected $fillable = [
        'user_id',
        'filename_id',
        'file_name',
        'path',
        'expirated_at',
    ];

    public function filename()
    {
        return $this->belongsTo(Filenames::class, 'filename_id');
    }

    public function verifyPath()
    {
        if(!empty($this->path)) {
            if (Storage::disk('public')->exists($this->path)) {
                return true;
            }
        }
        return false;
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
