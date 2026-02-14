<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $table = 'question';
    protected $fillable = [
        'id',
        'question',
        'answer_default',
        'question_option_id'
    ];

    public function option() {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }
}
