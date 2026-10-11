<?php

// This entry point deliberately works before vendor/ exists.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

chdir(__DIR__);
if (in_array('--help', $argv, true)) {
    echo "Interaktive Einrichtung: php setup.php\nBenötigt PHP >= 8.3, Composer und für Frontend-Assets Node.js/npm.\n";
    exit(0);
}
if (PHP_VERSION_ID < 80300) {
    fwrite(STDERR, "PHP 8.3 oder neuer erforderlich.\n");
    exit(1);
}
function setupAnswer(string $question, string $default): string
{
    echo "$question [$default]: ";
    $line = fgets(STDIN);
    if ($line === false) {
        fwrite(STDERR, "Einrichtung benötigt interaktive Eingaben.\n");
        exit(1);
    }

    return trim($line) ?: $default;
}
function setupConfirm(string $question): bool
{
    do {
        $answer = strtolower(setupAnswer($question.' (ja/nein)', 'nein'));
    } while (! in_array($answer, ['ja', 'j', 'nein', 'n'], true));

    return in_array($answer, ['ja', 'j'], true);
}
function setupRun(string $command): void
{
    passthru($command, $code);
    if ($code !== 0) {
        fwrite(STDERR, "Schritt fehlgeschlagen. Nach Behebung erneut php setup.php ausführen.\n");
        exit($code);
    }
}

echo "Laravel-Einrichtung — vorhandene Daten und Anwendungsschlüssel bleiben erhalten.\n";
do {
    $mode = strtolower(setupAnswer('Umgebung: entwicklung oder produktion', 'entwicklung'));
} while (! in_array($mode, ['entwicklung', 'produktion'], true));

if (! file_exists('.env')) {
    if (setupConfirm('Soll die .env aus der Projektvorlage erstellt werden?')) {
        if (! copy('.env.example', '.env')) {
            exit(1);
        }
        if (PHP_OS_FAMILY !== 'Windows') {
            chmod('.env', 0600);
        }
    }
} else {
    echo "Vorhandene .env wird weiterverwendet.\n";
}
if (setupConfirm('Sollen die PHP-Abhängigkeiten mit Composer installiert werden?')) {
    setupRun('composer install --no-interaction'.($mode === 'produktion' ? ' --no-dev --optimize-autoloader' : ''));
}
if (! file_exists('vendor/autoload.php') || ! file_exists('.env')) {
    echo "Weitere Schritte benötigen vendor/ und .env. Fehlende Schritte beim nächsten Aufruf bestätigen.\n";
    exit(1);
}
setupRun(escapeshellarg(PHP_BINARY).' artisan app:setup --environment='.($mode === 'produktion' ? 'production' : 'local'));
