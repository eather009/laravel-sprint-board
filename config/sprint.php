<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User model
    |--------------------------------------------------------------------------
    |
    | Eloquent model used for sprint leaders, members, and audit columns.
    |
    */
    'user_model' => env('SPRINT_USER_MODEL', 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Table prefix
    |--------------------------------------------------------------------------
    |
    | Optional prefix for package tables (e.g. "sprint_" → sprint_sprints).
    | Empty string keeps table names as sprints, sprint_members, sprint_issues.
    |
    */
    'table_prefix' => env('SPRINT_TABLE_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | HTTP
    |--------------------------------------------------------------------------
    */
    'route_prefix' => 'api/sprints',

    'middleware' => ['api', 'auth:sanctum'],

    /*
    |--------------------------------------------------------------------------
    | Throttle (Laravel throttle middleware rates: maxAttempts,decayMinutes)
    |--------------------------------------------------------------------------
    */
    'throttle' => [
        'refresh' => env('SPRINT_THROTTLE_REFRESH', '10,1'),
        'priority_sync' => env('SPRINT_THROTTLE_PRIORITY_SYNC', '10,1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    |
    | Gate / ability name that grants sprint-admin privileges when present.
    |
    */
    'admin_gate' => 'sprint-admin',

    /*
    |--------------------------------------------------------------------------
    | Priorities (generic map; tracker drivers may override)
    |--------------------------------------------------------------------------
    */
    'priorities' => [
        2 => 'High',
        3 => 'Normal',
        4 => 'Low',
    ],

    'default_priority_id' => 3,

    /*
    |--------------------------------------------------------------------------
    | Default issue tracker
    |--------------------------------------------------------------------------
    */
    'tracker_default' => 'backlog', // backlog|null

    /*
    |--------------------------------------------------------------------------
    | Backlog driver
    |--------------------------------------------------------------------------
    */
    'backlog' => [
        'space_url' => env('BACKLOG_SPACE_URL'),
        'api_key' => env('BACKLOG_API_KEY'),
        'http_timeout' => (int) env('BACKLOG_HTTP_TIMEOUT', 15),
        'closed_status_ids' => [4, 5],
        'priorities' => [
            2 => 'High',
            3 => 'Normal',
            4 => 'Low',
        ],
        'default_priority_id' => 3,
        'hydrate_cache_ttl_hours' => 3,
        'my_tasks_cache_ttl_hours' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard widgets catalog (payload shape filled in later phases)
    |--------------------------------------------------------------------------
    */
    'dashboard_widgets' => [
        'progress',
        'by_member',
        'by_priority',
        'completion',
    ],
];
