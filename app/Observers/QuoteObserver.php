<?php

namespace App\Observers;

use App\Models\Quote;
use App\Models\QuoteHistory;
use Illuminate\Support\Facades\Auth;

class QuoteObserver
{
    protected $trackedColumns = ['priority', 'status', 'value'];
    
    /**
     * Handle the Quote "created" event.
     */
    public function created(Quote $quote)
    {
        foreach ($this->trackedColumns as $column) {
            if ($quote->$column !== null) { // Registra todos los valores iniciales
                $this->createHistoryRecord(
                    quote: $quote,
                    column: $column,
                    oldValue: null,
                    newValue: $quote->$column,
                    operationType: 'created'
                );
            }
        }
    }

    /**
     * Handle the Quote "updated" event.
     */
    public function updated(Quote $quote)
    {
        $original = $quote->getOriginal();
        
        foreach ($this->trackedColumns as $column) {
            if ($quote->isDirty($column)) {
                $this->createHistoryRecord(
                    quote: $quote,
                    column: $column,
                    oldValue: $original[$column],
                    newValue: $quote->$column,
                    operationType: 'updated'
                );
            }
        }
    }

    /**
     * Crea registro de historial estandarizado
     */
    protected function createHistoryRecord(Quote $quote, string $column, $oldValue, $newValue, string $operationType)
    {
        QuoteHistory::create([
            'quote_id' => $quote->id,
            'changed_column' => $column,
            'old_value' => $this->formatValue($oldValue),
            'new_value' => $this->formatValue($newValue),
            'user_id' => Auth::id(),
            'operation_type' => $operationType,
            'created_at' => now()
        ]);
    }

    /**
     * Formatea valores para almacenamiento consistente
     */
    protected function formatValue($value)
    {
        if ($value === null) {
            return null;
        }

        // Si es un Enum, obtener su valor
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        return $value;
    }
}