<?php

namespace App\Observers;

use App\Models\DatabaseLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ModelObserver
{
    public function created(Model $model)
    {
        $this->logChange($model, 'created');
    }

    public function updated(Model $model)
    {
        $this->logChange($model, 'updated');
    }

    public function deleted(Model $model)
    {
        $this->logChange($model, 'deleted');
    }

    protected function logChange(Model $model, string $event)
{
    $queries = DB::getQueryLog();
    $lastQuery = end($queries);
    
    // Validación adicional
    $sql = $lastQuery ? vsprintf(
        str_replace('?', "'%s'", $lastQuery['query']), 
        $lastQuery['bindings']
    ) : 'No SQL captured';

    DatabaseLog::create([
        'user_id' => auth()->id(),
        'model_type' => get_class($model),
        'model_id' => $model->id,
        'sql_query' => $sql, // Asegurar que siempre tenga valor
        'event' => $event
    ]);
}

    protected function getLastQuery()
    {
        $queries = DB::getQueryLog();
        $lastQuery = end($queries);
        
        return vsprintf(
            str_replace('?', "'%s'", $lastQuery['query']), 
            $lastQuery['bindings']
        );
    }
}