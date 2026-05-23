<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteHistory extends Model
{
    protected $table = 'quote_histories';
    protected $fillable = [
        'quote_id',
        'changed_column',
        'old_value',
        'new_value',
        'user_id'
    ];
}