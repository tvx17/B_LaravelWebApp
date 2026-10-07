<?php

return [
    // Master switch. Off unless explicitly enabled.
    'enabled' => env('ARTISAN_RUNNER_UI_ENABLED', false),

    'path' => env('ARTISAN_RUNNER_UI_PATH', 'artisan-runner-ui'),

    // Add your own, e.g. 'can:run-artisan-runner-ui'
    'middleware' => ['web', 'auth', 'can:run-artisan-runner-ui'],

    // Anything else returns 404
    'environments' => ['local'],

    // If not empty, ONLY these commands are shown/runnable (wildcards allowed)
    'allowed' => ['about', 'migrate:status', 'permission:show', 'permission:cache-reset'],

    // Never shown or runnable (wildcards allowed)
    'denied' => [
        'tinker', 'serve', 'down', 'up',
        'db:wipe', 'migrate:fresh', 'migrate:reset', 'migrate:refresh', 'migrate:rollback',
        'key:generate', 'env:*', 'queue:work', 'queue:listen', 'schedule:work',
        'schedule:run', 'vendor:publish', 'package:discover',
    ],
];
