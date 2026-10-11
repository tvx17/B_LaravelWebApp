<?php

return [
    'driver' => 'file',
    'memory_limit' => env('LARAVEL_BRAIN_MEMORY_LIMIT', '1024M'),
    'execution_time_limit' => 300,
    'auto_discover_routes' => true,
    'auto_discover_exclude_vendor' => true,
    'mcp' => ['enabled' => false],
];
