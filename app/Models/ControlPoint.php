<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\QueuedWriter;

class ControlPoint extends Model
{
    use HasFactory;

    protected $table = 'control_point';

    protected $fillable = [
        'id',
        'name',
        'color',
        'device_id',
        'code'
    ];

    public function product()
    {
        return $this->belongsTo(ProductCatalog::class, 'device_id');
    }

    public function products()
    {
        return $this->hasManyThrough(
            ProductCatalog::class,
            ControlPointProduct::class,
            'control_point_id',
            'id',
            'id',
            'product_id'
        );
    }

    public function hasProduct($productId)
    {
        return $this->products->contains('id', $productId);
    }

    public function questions()
    {
        return $this->hasManyThrough(
            Question::class,
            ControlPointQuestion::class,
            'control_point_id',
            'id',
            'id',
            'question_id'
        )->orderBy('id', 'asc');
    }

    public function hasQuestion($question_id) {
        return $this->questions->contains('id', $question_id);
    }

    public function pests()
    {
        return $this->hasManyThrough(
            PestCatalog::class,
            ControlPointPest::class,
            'control_point_id',
            'id',
            'id',
            'pest_id'
        );
    }
    public function hasPest($pestId)
    {
        return $this->pests->contains('id', $pestId);
    }

    public function pest()
    {
        return $this->belongsTo(PestCatalog::class, 'device_id');
    }
}
