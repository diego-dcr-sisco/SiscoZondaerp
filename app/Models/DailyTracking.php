<?php

namespace App\Models;

use App\Enums\DailyTrackingClosed;
use App\Enums\DailyTrackingContactMethod;
use App\Enums\DailyTrackingCustomerType;
use App\Enums\DailyTrackingInvoice;
use App\Enums\DailyTrackingPaymentMethod;
use App\Enums\DailyTrackingQuoted;
use App\Enums\DailyTrackingServiceType;
use App\Enums\DailyTrackingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DailyTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'customer_name',
        'phone',
        'customer_type',
        'state',
        'city',
        'address',
        'contact_method',
        'status',
        'service_type',
        'responded',
        'quoted',
        'closed',
        'has_coverage',
        'quoted_amount',
        'billed_amount',
        'payment_method',
        'invoice',
        'service_date',
        'quote_sent_date',
        'close_date',
        'payment_date',
        'follow_up_date',
        'service_time',
        'notes',
        'status_updated_at',
        'status_updated_by',
    ];

    protected $casts = [
        'customer_type' => DailyTrackingCustomerType::class,
        'contact_method' => DailyTrackingContactMethod::class,
        'status' => DailyTrackingStatus::class,
        'service_type' => DailyTrackingServiceType::class,
        'quoted' => DailyTrackingQuoted::class,
        'closed' => DailyTrackingClosed::class,
        'payment_method' => DailyTrackingPaymentMethod::class,
        'invoice' => DailyTrackingInvoice::class,
        'responded' => 'boolean',
        'has_coverage' => 'boolean',
        'quoted_amount' => 'decimal:2',
        'billed_amount' => 'decimal:2',
        'service_date' => 'date',
        'quote_sent_date' => 'date',
        'close_date' => 'date',
        'payment_date' => 'date',
        'follow_up_date' => 'date',
        'status_updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (DailyTracking $dailyTracking) {
            $trackedFields = ['status', 'quoted', 'closed'];

            foreach ($trackedFields as $field) {
                if (! $dailyTracking->isDirty($field)) {
                    continue;
                }

                $oldValue = $dailyTracking->getOriginal($field);
                $newValue = $dailyTracking->{$field};

                if ($oldValue instanceof \BackedEnum) {
                    $oldValue = $oldValue->value;
                }

                if ($newValue instanceof \BackedEnum) {
                    $newValue = $newValue->value;
                }

                DailyTrackingLog::create([
                    'daily_tracking_id' => $dailyTracking->id,
                    'field' => $field,
                    'old_value' => $oldValue !== null ? (string) $oldValue : null,
                    'new_value' => $newValue !== null ? (string) $newValue : null,
                    'changed_by' => Auth::id(),
                ]);

                if ($field === 'status') {
                    $dailyTracking->status_updated_at = now();
                    $dailyTracking->status_updated_by = Auth::id();
                }
            }
        });
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function logs()
    {
        return $this->hasMany(DailyTrackingLog::class);
    }
}
