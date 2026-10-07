<?php

return [
    // All other options retain the package defaults.
    'enabled' => env('APP_ENV', 'production') === 'local'
        && (bool) env('APP_DEBUG', false)
        && (bool) env('DEBUGBAR_ENABLED', true),
];
