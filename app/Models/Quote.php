<?php

namespace App\Models;

use App\Enums\QuoteStatus;
use App\Enums\QuotePriority;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Quote extends Model
{
    protected $table = 'quote';
    
    protected $casts = [
        'status' => QuoteStatus::class,
        'priority' => QuotePriority::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'valid_until' => 'date',
        'value' => 'float',
    ];
    
    protected $attributes = [
        'status' => QuoteStatus::DRAFT->value,
        'priority' => QuotePriority::MEDIUM->value, // Valor por defecto corregido
        'value' => 0.0, // Valor por defecto para 'value'
    ];

    protected $fillable = [
        'service_id',
        'model_id',
        'model_type',
        'start_date',
        'end_date',
        'valid_until',
        'value',
        'priority',
        'status',
        'probability',
        'comments',
        'file'
    ];

    public function model()
    {
        return $this->morphTo();
    }
    
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    
    public function histories()
    {
        return $this->hasMany(QuoteHistory::class);
    }
    
    protected function probability(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ?? $this->calculateProbability(),
        );
    }
    
    public function changeStatus(QuoteStatus $newStatus, string $notes = null)
    {
        if (!in_array($newStatus, $this->status->allowedTransitions())) {
            throw new \Exception("Transición de estado no permitida");
        }
        
        if ($newStatus === QuoteStatus::SENT && !$this->valid_until) {
            throw new \Exception("Debe establecer una fecha de validez");
        }
        
        if ($newStatus === QuoteStatus::CONVERTED && !$this->approved_at) {
            throw new \Exception("La cotización debe estar aprobada primero");
        }
        
        \DB::transaction(function () use ($newStatus, $notes) {
            $this->status = $newStatus;
            
            match($newStatus) {
                QuoteStatus::SENT => $this->sent_at = now(),
                QuoteStatus::APPROVED => $this->approved_at = now(),
                QuoteStatus::CONVERTED => $this->converted_at = now(),
                default => null,
            };
            
            $this->save();
            
            $this->histories()->create([
                'changed_column' => 'status',
                'old_value' => $this->getOriginal('status'),
                'new_value' => $newStatus->value,
                'user_id' => auth()->id(),
                'notes' => $notes
            ]);
            
            if ($newStatus === QuoteStatus::SENT) {
                event(new QuoteSent($this));
            }
        });
        
        return $this;
    }
    
    protected function calculateProbability(): int
    {
        $baseProbability = match($this->status) {
            QuoteStatus::DRAFT => 10,
            QuoteStatus::SENT => 30,
            QuoteStatus::REVIEWED => 50,
            QuoteStatus::APPROVED => 80,
            QuoteStatus::CONVERTED => 100,
            default => 0,
        };
        
        // Ajustar por prioridad
        return $baseProbability + match($this->priority) {
            QuotePriority::LOW => -10,
            QuotePriority::MEDIUM => 0,
            QuotePriority::HIGH => 15,
            QuotePriority::URGENT => 25,
        };
    }
    
    public function getPriorityLabel(): string
    {
        return $this->priority->label();
    }

    public function getPriorityColor(): string
    {
        return $this->priority->color();
    }
}