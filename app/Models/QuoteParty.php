<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteParty extends Model
{
    protected $table = 'quote_parties';

    protected $fillable = [
        'quote_id',
        'role',
        'name',
        'business_name',
        'attention',
        'rfc',
        'phone',
        'email',
        'address',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}