<?php

return [
    'kopia_binary' => env('KOPIA_BINARY', 'kopia'),
    'timeout' => 3600,
    'staging_directory' => storage_path('app/backup-staging'),
];
