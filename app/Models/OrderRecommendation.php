<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderRecommendation extends Model
{
    use HasFactory;

    // Definimos la tabla manualmente ya que no sigue la convención de nombres plurales de Laravel
    protected $table = 'order_recommendation';

    // Permitimos la asignación masiva de estos campos
    protected $fillable = [
        'id',
        'order_id',
        'service_id',
        'recommendation_id',
        'recommendation_text',
    ];

    // Relación con el modelo 'Order'
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Relación con el modelo 'Recommendation'
    public function recommendation()
    {
        return $this->belongsTo(Recommendations::class);
    }
}
