<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatabaseLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

     //protected $table = 'database_log_siscoplagas';
     protected $table = 'database_log_siscoplagas';

    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
        'sql_query',
        'event',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * Obtiene el usuario que realizó la acción
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación polimórfica con cualquier modelo
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Scope para filtrar por tipo de modelo
     */
    public function scopeForModel($query, $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope para filtrar por evento
     */
    public function scopeForEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope para filtrar por usuario
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Obtiene los logs más recientes primero
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Formatea el SQL para mejor visualización
     */
    public function getFormattedSqlAttribute()
    {
        return preg_replace('/\s+/', ' ', $this->sql_query);
    }

    /**
     * Obtiene el nombre simple del modelo (sin namespace)
     */
    public function getSimpleModelTypeAttribute()
    {
        return class_basename($this->model_type);
    }
}