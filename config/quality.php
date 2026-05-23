<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Order Status Constants
    |--------------------------------------------------------------------------
    |
    | These constants define the different status values for orders
    |
    */
    'order_status' => [
        'pending' => 1,
        'accepted' => 2,
        'finished' => 3,
        'verified' => 4,
        'approved' => 5,
        'canceled' => 6,
    ],

    /*
    |--------------------------------------------------------------------------
    | Device Consumption Levels
    |--------------------------------------------------------------------------
    |
    | These constants define the consumption levels for device analytics
    |
    */
    'consumption_levels' => [
        'null' => 0,
        'low' => 0.25,
        'medium' => 0.5,
        'high' => 0.75,
        'very_high' => 1.0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for analytics and reporting features
    |
    */
    'analytics' => [
        'default_date_range' => 30, // days
        'max_results_per_page' => 100,
        'cache_duration' => 3600, // seconds
        'enable_caching' => env('QUALITY_ANALYTICS_CACHE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Common validation rules used across quality modules
    |
    */
    'validation' => [
        'customer_id' => 'required|integer|exists:customer,id',
        'order_id' => 'required|integer|exists:order,id',
        'device_id' => 'required|integer|exists:device,id',
        'product_id' => 'required|integer|exists:product_catalog,id',
        'quantity' => 'required|numeric|min:0.01|max:9999.99',
        'unit_price' => 'required|numeric|min:0|max:999999.99',
        'date_range' => 'nullable|string|regex:/^\d{2}\/\d{2}\/\d{4} - \d{2}\/\d{2}\/\d{4}$/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Labels
    |--------------------------------------------------------------------------
    |
    | Human-readable labels for status values
    |
    */
    'status_labels' => [
        1 => 'Pendiente',
        2 => 'Aceptada',
        3 => 'Terminada',
        4 => 'Verificada',
        5 => 'Aprobada',
        6 => 'Cancelada',
    ],

    /*
    |--------------------------------------------------------------------------
    | Consumption Level Labels
    |--------------------------------------------------------------------------
    |
    | Human-readable labels for consumption levels
    |
    */
    'consumption_labels' => [
        'null' => 'Nulo',
        'low' => 'Bajo',
        'medium' => 'Medio',
        'high' => 'Alto',
        'very_high' => 'Muy Alto',
    ],
]; 