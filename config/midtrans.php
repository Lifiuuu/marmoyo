<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for Midtrans Payment Gateway Core API.
    | Set is_production to true when deploying with production credentials.
    |
    */

    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),
    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),

    /*
    | API Base URL
    */
    'base_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://api.midtrans.com'
        : 'https://api.sandbox.midtrans.com',
];

