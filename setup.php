<?php

// Compatibility entry point, independent of vendor/ and Laravel.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
if (in_array('--help', $argv, true)) {
    echo "Interaktive Einrichtung: php setup.php oder composer setup\nAlternativ: php scripts/Setup.php\n";
    exit(0);
}
require __DIR__.'/scripts/Setup.php';
