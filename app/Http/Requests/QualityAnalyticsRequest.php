<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QualityAnalyticsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('view-quality-analytics');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => config('quality.validation.customer_id'),
            'date_range' => config('quality.validation.date_range'),
            'service_id' => 'nullable|integer|exists:service,id',
            'technician_id' => 'nullable|integer|exists:technician,id',
            'status_id' => 'nullable|integer|in:' . implode(',', array_values(config('quality.order_status'))),
            'device_id' => 'nullable|integer|exists:device,id',
            'product_id' => 'nullable|integer|exists:product_catalog,id',
            'sort_by' => 'nullable|string|in:programmed_date,created_at,folio,status_id',
            'sort_direction' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:10|max:' . config('quality.analytics.max_results_per_page'),
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'El ID del cliente es requerido.',
            'customer_id.exists' => 'El cliente especificado no existe.',
            'date_range.regex' => 'El formato de fecha debe ser DD/MM/YYYY - DD/MM/YYYY.',
            'service_id.exists' => 'El servicio especificado no existe.',
            'technician_id.exists' => 'El técnico especificado no existe.',
            'status_id.in' => 'El estado especificado no es válido.',
            'device_id.exists' => 'El dispositivo especificado no existe.',
            'product_id.exists' => 'El producto especificado no existe.',
            'sort_by.in' => 'El campo de ordenamiento no es válido.',
            'sort_direction.in' => 'La dirección de ordenamiento debe ser asc o desc.',
            'per_page.min' => 'El número mínimo de resultados por página es 10.',
            'per_page.max' => 'El número máximo de resultados por página es ' . config('quality.analytics.max_results_per_page') . '.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_id' => 'ID del cliente',
            'date_range' => 'rango de fechas',
            'service_id' => 'servicio',
            'technician_id' => 'técnico',
            'status_id' => 'estado',
            'device_id' => 'dispositivo',
            'product_id' => 'producto',
            'sort_by' => 'ordenar por',
            'sort_direction' => 'dirección de ordenamiento',
            'per_page' => 'resultados por página',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new \Illuminate\Validation\ValidationException($validator, response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos',
                'errors' => $validator->errors()
            ], 422));
        }

        parent::failedValidation($validator);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert customer parameter to customer_id if needed
        if ($this->route('customer') && !$this->has('customer_id')) {
            $this->merge([
                'customer_id' => $this->route('customer')
            ]);
        }

        // Set default values
        $this->merge([
            'sort_by' => $this->input('sort_by', 'programmed_date'),
            'sort_direction' => $this->input('sort_direction', 'desc'),
            'per_page' => $this->input('per_page', 20),
        ]);
    }
} 