<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supabase Project Configuration
    |--------------------------------------------------------------------------
    |
    | Credentials and API endpoints for Supabase Cloud services.
    | User can populate these anytime in the .env file.
    |
    */
    'url' => env('SUPABASE_URL', ''),
    'anon_key' => env('SUPABASE_ANON_KEY', ''),
    'service_role_key' => env('SUPABASE_SERVICE_ROLE_KEY', ''),
    'storage_bucket' => env('SUPABASE_STORAGE_BUCKET', 'marmoyo-assets'),

    'db' => [
        'host' => env('SUPABASE_DB_HOST', ''),
        'port' => env('SUPABASE_DB_PORT', 5432),
        'database' => env('SUPABASE_DB_DATABASE', 'postgres'),
        'username' => env('SUPABASE_DB_USERNAME', 'postgres'),
        'password' => env('SUPABASE_DB_PASSWORD', ''),
    ],
];

