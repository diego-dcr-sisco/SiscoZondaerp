<?php

namespace App\Services;

use App\Models\DatabaseLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LoggingService
{
    /**
     * Event types for different actions
     */
    const EVENT_CUSTOMER_CREATED = 'customer_created';
    const EVENT_LEAD_CREATED = 'lead_created';
    const EVENT_LEAD_CONVERTED = 'lead_converted';
    const EVENT_ORDER_CREATED = 'order_created';
    const EVENT_ORDER_COMPLETED = 'order_completed';
    const EVENT_ORDER_APPROVED = 'order_approved';
    const EVENT_SCHEDULE_CREATED = 'schedule_created';

    /**
     * Log an action in the system
     *
     * @param string $eventType The type of event being logged
     * @param Model $model The model being affected
     * @param array $additionalData Additional context data
     * @param User|null $user The user performing the action (defaults to authenticated user)
     * @return DatabaseLog
     */
    public function log(string $eventType, Model $model, array $additionalData = [], ?User $user = null): DatabaseLog
    {
        $user = $user ?? Auth::user();

        return DatabaseLog::create([
            'user_id' => $user?->id,
            'event_type' => $eventType,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'data' => json_encode([
                'changes' => $model->getDirty(),
                'additional' => $additionalData,
            ]),
            'changetype' => $this->determineChangeType($eventType),
            'change' => $this->generateChangeDescription($eventType, $model),
        ]);
    }

    /**
     * Log customer creation
     */
    public function logCustomerCreated(Model $customer, ?User $creator = null): DatabaseLog
    {
        return $this->log(
            self::EVENT_CUSTOMER_CREATED,
            $customer,
            ['creator_id' => $creator?->id],
            $creator
        );
    }

    /**
     * Log lead creation
     */
    public function logLeadCreated(Model $lead, ?User $creator = null): DatabaseLog
    {
        return $this->log(
            self::EVENT_LEAD_CREATED,
            $lead,
            ['creator_id' => $creator?->id],
            $creator
        );
    }

    /**
     * Log lead conversion to customer
     */
    public function logLeadConverted(Model $lead, Model $customer, ?User $converter = null): DatabaseLog
    {
        return $this->log(
            self::EVENT_LEAD_CONVERTED,
            $customer,
            [
                'lead_id' => $lead->id,
                'converter_id' => $converter?->id
            ],
            $converter
        );
    }

    /**
     * Log service order creation
     */
    public function logOrderCreated(Model $order, ?User $creator = null): DatabaseLog
    {
        return $this->log(
            self::EVENT_ORDER_CREATED,
            $order,
            [
                'customer_id' => $order->customer_id,
                'creator_id' => $creator?->id
            ],
            $creator
        );
    }

    /**
     * Log service order completion
     */
    public function logOrderCompleted(Model $order, ?User $completer = null): DatabaseLog
    {
        return $this->log(
            self::EVENT_ORDER_COMPLETED,
            $order,
            [
                'customer_id' => $order->customer_id,
                'completer_id' => $completer?->id,
                'completion_date' => now()
            ],
            $completer
        );
    }

    /**
     * Log service order approval
     */
    public function logOrderApproved(Model $order, ?User $approver = null): DatabaseLog
    {
        return $this->log(
            self::EVENT_ORDER_APPROVED,
            $order,
            [
                'customer_id' => $order->customer_id,
                'approver_id' => $approver?->id,
                'approval_date' => now()
            ],
            $approver
        );
    }

    /**
     * Log schedule creation
     */
    public function logScheduleCreated(Model $schedule, ?User $creator = null): DatabaseLog
    {
        return $this->log(
            self::EVENT_SCHEDULE_CREATED,
            $schedule,
            [
                'creator_id' => $creator?->id,
                'schedule_date' => $schedule->date
            ],
            $creator
        );
    }

    /**
     * Determine the change type based on event type
     */
    private function determineChangeType(string $eventType): string
    {
        return match ($eventType) {
            self::EVENT_CUSTOMER_CREATED, 
            self::EVENT_LEAD_CREATED, 
            self::EVENT_ORDER_CREATED, 
            self::EVENT_SCHEDULE_CREATED => 'create',
            
            self::EVENT_LEAD_CONVERTED => 'convert',
            
            self::EVENT_ORDER_COMPLETED, 
            self::EVENT_ORDER_APPROVED => 'update',
            
            default => 'other',
        };
    }

    /**
     * Generate a human-readable change description
     */
    private function generateChangeDescription(string $eventType, Model $model): string
    {
        return match ($eventType) {
            self::EVENT_CUSTOMER_CREATED => "Created new customer: {$model->name}",
            self::EVENT_LEAD_CREATED => "Created new lead: {$model->name}",
            self::EVENT_LEAD_CONVERTED => "Converted lead to customer: {$model->name}",
            self::EVENT_ORDER_CREATED => "Created new service order #{$model->id}",
            self::EVENT_ORDER_COMPLETED => "Completed service order #{$model->id}",
            self::EVENT_ORDER_APPROVED => "Approved service order #{$model->id}",
            self::EVENT_SCHEDULE_CREATED => "Created new schedule for " . $model->date->format('Y-m-d'),
            default => "Unknown action performed",
        };
    }
} 