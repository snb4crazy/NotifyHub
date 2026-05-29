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
        'enabled' => env('NOTIFYHUB_PUSH_ENABLED', true),
        'minimum_severity' => env('NOTIFYHUB_PUSH_MIN_SEVERITY', 'error'),
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

