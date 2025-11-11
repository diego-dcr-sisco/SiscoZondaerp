<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sat' => [
        'rfc' => env('SAT_RFC'),
        'business_name' => env('SAT_BUSINESS_NAME'),
        'tax_regime' => env('SAT_TAX_REGIME'),
        'tax_regime_name' => env('SAT_TAX_REGIME_NAME'),
        'password' => env('SAT_CERT_PASSWORD'),
        'address' => env('SAT_ADDRESS'),
        'zip_code' => env('SAT_ZIP_CODE'),
        'city' => env('SAT_CITY'),
        'state' => env('SAT_STATE'),
        'country' => env('SAT_COUNTRY'),
        'employer_registration' => env('SAT_EMPLOYER_REGISTRATION'),
    ],


    'facturama' => [
        'rfc' => env('SAT_RFC'),
        'business_name' => env('SAT_BUSINESS_NAME'),
        'fiscal_regime' => env('SAT_FISCAL_REGIME'),
        'zip_code' => env('SAT_ZIP_CODE'),

        'auth' => [
            'user' => env('FACTURAMA_USER'),
            'password' => env('FACTURAMA_PASSWORD'),
            'mode' => env('FACTURAMA_MODE', 'testing'),
            'endpoint' => env('FACTURAMA_ENDPOINT'),
        ]
    ],

    'company' => [
        'phone' => env('COMPANY_PHONE'),
        'address' => env('COMPANY_ADDRESS'),
        'zip_code' => env('COMPANY_ZIP_CODE'),
        'city' => env('COMPANY_CITY'),
        'state' => env('COMPANY_STATE') ?? env('COMPANY_CITY') . ' (Estado)',
        'country' => env('COMPANY_COUNTRY'),
        'sanitary_license' => env('COMPANY_SANITARY_LICENSE'),
        'sanitary_license_2' => env('COMPANY_SANITARY_LICENSE_2'),
        'economic_activity' => env('COMPANY_ECONOMIC_ACTIVITY'),
    ],
];
