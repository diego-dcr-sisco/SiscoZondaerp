<?php

namespace App\Traits;

use App\Models\DatabaseLog;
use App\Services\LoggingService;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Loggable
{
    /**
     * Get all logs for this model
     */
    public function logs(): MorphMany
    {
        return $this->morphMany(DatabaseLog::class, 'model');
    }

    /**
     * Get the logging service instance
     */
    protected function logger(): LoggingService
    {
        return app(LoggingService::class);
    }

    /**
     * Log an action for this model
     */
    public function logAction(string $eventType, array $additionalData = []): DatabaseLog
    {
        return $this->logger()->log($eventType, $this, $additionalData);
    }
} 