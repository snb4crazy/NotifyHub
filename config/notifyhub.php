<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Operational mode
    |--------------------------------------------------------------------------
    |
    | single - one owner or one small team, quickest MVP setup.
    | team   - multiple users, project memberships, and granular roles.
    |
    */
    'mode' => env('NOTIFYHUB_MODE', 'single'),

    /*
    |--------------------------------------------------------------------------
    | Default bootstrap values
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'project_name' => env('NOTIFYHUB_DEFAULT_PROJECT_NAME', 'Personal Alerts'),
        'project_slug' => env('NOTIFYHUB_DEFAULT_PROJECT_SLUG', 'personal-alerts'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Push dispatch configuration
    |--------------------------------------------------------------------------
    */
    'push' => [
        'driver' => env('NOTIFYHUB_PUSH_DRIVER', 'log'),
        'enabled' => env('NOTIFYHUB_PUSH_ENABLED', true),
        'minimum_severity' => env('NOTIFYHUB_PUSH_MIN_SEVERITY', 'error'),
        'fcm' => [
            'project_id' => env('NOTIFYHUB_FCM_PROJECT_ID'),
            'client_email' => env('NOTIFYHUB_FCM_CLIENT_EMAIL'),
            'private_key' => env('NOTIFYHUB_FCM_PRIVATE_KEY'),
            'credentials_path' => env('NOTIFYHUB_FCM_CREDENTIALS_PATH'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Mobile API
    |--------------------------------------------------------------------------
    */
    'mobile' => [
        'token_name' => env('NOTIFYHUB_MOBILE_TOKEN_NAME', 'mobile'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security and redaction
    |--------------------------------------------------------------------------
    */
    'security' => [
        'ingest_header' => env('NOTIFYHUB_INGEST_HEADER', 'X-Project-Key'),
        'sensitive_roles' => explode(',', env('NOTIFYHUB_SENSITIVE_ROLES', 'owner,admin,triager')),
    ],
];

