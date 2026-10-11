<?php

// Copy to web-setup.local.php in the project root (outside public/) before upload.
return [
    // Choose a private, random access code with at least 32 characters.
    'access_token' => '',
    // Enable only if your hosting permits these programs to run from PHP.
    'allow_processes' => false,
    'composer' => ['composer'], // Alternatively ['php', '/absolute/path/composer.phar'].
    'npm' => ['npm'],
];
