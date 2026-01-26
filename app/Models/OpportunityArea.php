<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpportunityArea extends Model
{
    use HasFactory;

    // Tabla asociada al modelo
    protected $table = 'opportunity_area';

    // Atributos asignables en masa
    protected $fillable = [
        'customer_id',
        'application_area_id',
        'date',
        'estimated_date',
        'opportunity',
        'recommendation',
        'status',
        'tracing',
        'img_incidence',
        'img_conclusion',
    ];

    private $tracing_options = ['Pendiente', 'En proceso', 'Concluido'];
    private $status_options = ['Abierto', 'Cerrado'];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function applicationArea()
    {
        return $this->belongsTo(ApplicationArea::class);
    }

    public function getTracing()
    {
        return $this->tracing_options[$this->tracing];
    }

    public function getStatus()
    {
        return $this->status_options[$this->status];
    }
}
