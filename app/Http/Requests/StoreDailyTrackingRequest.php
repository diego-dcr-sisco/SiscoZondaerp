<?php

namespace App\Http\Requests;

use App\Enums\DailyTrackingClosed;
use App\Enums\DailyTrackingContactMethod;
use App\Enums\DailyTrackingCustomerType;
use App\Enums\DailyTrackingInvoice;
use App\Enums\DailyTrackingPaymentMethod;
use App\Enums\DailyTrackingQuoted;
use App\Enums\DailyTrackingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreDailyTrackingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', Rule::exists('service', 'id')],
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'customer_type' => ['required', new Enum(DailyTrackingCustomerType::class)],
            'customer_category' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'contact_method' => ['required', new Enum(DailyTrackingContactMethod::class)],
            'status' => ['required', new Enum(DailyTrackingStatus::class)],
            'not_responded' => ['nullable', 'boolean'],
            'is_recurrent' => ['nullable', 'boolean'],
            'quoted' => ['required', new Enum(DailyTrackingQuoted::class)],
            'closed' => ['required', new Enum(DailyTrackingClosed::class)],
            'has_not_coverage' => ['nullable', 'boolean'],
            'quoted_amount' => ['nullable', 'decimal:0,2'],
            'billed_amount' => ['nullable', 'decimal:0,2'],
            'payment_method' => ['nullable', new Enum(DailyTrackingPaymentMethod::class)],
            'invoice' => ['required', new Enum(DailyTrackingInvoice::class)],
            'service_date' => ['nullable', 'date'],
            'quote_sent_date' => ['nullable', 'date'],
            'close_date' => ['nullable', 'date'],
            'payment_date' => ['nullable', 'date'],
            'follow_up_date' => ['nullable', 'date'],
            'service_time' => ['nullable', 'date_format:H:i'],
            'focused_pest' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $serviceTime = $this->input('service_time');

        $this->merge([
            'not_responded' => $this->boolean('not_responded'),
            'is_recurrent' => $this->boolean('is_recurrent'),
            'has_not_coverage' => $this->boolean('has_not_coverage'),
            'quoted_amount' => $this->input('quoted_amount') !== '' ? $this->input('quoted_amount') : null,
            'billed_amount' => $this->input('billed_amount') !== '' ? $this->input('billed_amount') : null,
            'payment_method' => $this->input('payment_method') !== '' ? $this->input('payment_method') : null,
            'service_time' => is_string($serviceTime) && strlen($serviceTime) >= 5 ? substr($serviceTime, 0, 5) : $serviceTime,
        ]);
    }
}
