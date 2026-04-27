<?php

namespace App\Models;

use App\Enums\DailyTrackingClosed;
use App\Enums\DailyTrackingContactMethod;
use App\Enums\DailyTrackingCustomerType;
use App\Enums\DailyTrackingInvoice;
use App\Enums\DailyTrackingPaymentMethod;
use App\Enums\DailyTrackingQuoted;
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
        'customer_category',
        'state',
        'city',
        'address',
        'contact_method',
        'status',
        'not_responded',
        'is_recurrent',
        'quoted',
        'closed',
        'has_not_coverage',
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
        'focused_pest',
        'notes',
        'status_updated_at',
        'status_updated_by',
    ];

    protected $casts = [
        'customer_type' => DailyTrackingCustomerType::class,
        'contact_method' => DailyTrackingContactMethod::class,
        'status' => DailyTrackingStatus::class,
        'quoted' => DailyTrackingQuoted::class,
        'closed' => DailyTrackingClosed::class,
        'payment_method' => DailyTrackingPaymentMethod::class,
        'invoice' => DailyTrackingInvoice::class,
        'not_responded' => 'boolean',
        'is_recurrent' => 'boolean',
        'has_not_coverage' => 'boolean',
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
            if ($dailyTracking->isDirty('status')) {
                $dailyTracking->status_updated_at = now();
                $dailyTracking->status_updated_by = Auth::id();
            }

            $dirtyFields = array_keys($dailyTracking->getDirty());

            foreach ($dirtyFields as $field) {
                if ($field === 'updated_at') {
                    continue;
                }

                $oldValue = self::normalizeLogValue($dailyTracking->getOriginal($field));
                $newValue = self::normalizeLogValue($dailyTracking->getAttribute($field));

                DailyTrackingLog::create([
                    'daily_tracking_id' => $dailyTracking->id,
                    'field' => $field,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                    'changed_by' => Auth::id(),
                ]);
            }
        });
    }

    private static function normalizeLogValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
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
