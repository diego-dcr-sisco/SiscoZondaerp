<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotePdfSnapshot extends Model
{
    protected $table = 'quote_pdf_snapshots';

    protected $casts = [
        'payload' => 'array',
        'generated_at' => 'datetime',
        'issued_date' => 'date',
        'valid_until' => 'date',
        'tax_percent' => 'float',
    ];

    protected $fillable = [
        'quote_id',
        'user_id',
        'version',
        'title',
        'quote_no',
        'currency',
        'issued_date',
        'valid_until',
        'tax_percent',
        'payload',
        'pdf_path',
        'generated_at',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
