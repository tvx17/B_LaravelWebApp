<?php

namespace App\Console\Commands;

use App\Support\SetupEnvironment;
use Dotenv\Dotenv;
use Illuminate\Console\Command;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use LaraMint\LaravelBrain\LaravelBrainServiceProvider;
use Laravel\Telescope\Telescope;
use RuntimeException;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class Setup extends Command
{
    protected $signature = 'app:setup {--environment=local}';

    protected $description = 'Projekt nach dem Klonen interaktiv einrichten';

    private array $notes = [];

    public function confirm($question, $default = false)
    {
        return $this->output->askQuestion(new ConfirmationQuestion(
            $question.' (ja/nein) ', $default, '/^(j|ja|y|yes)$/i',
        ));
    }

    public function handle(): int
    {
        if (! $this->input->isInteractive() || ! is_file(base_path('.env'))) {
            $this->error('Interaktive Eingaben und .env erforderlich. Bitte php setup.php verwenden.');

            return self::FAILURE;
        }
        try {
            if ($this->confirm('Soll die Anwendung (Umgebung und Basis-URL) konfiguriert werden?')) {
                $local = $this->option('environment') === 'local';
                do {
                    $url = $this->ask('Basis-URL', config('app.url'));
                } while (! filter_var($url, FILTER_VALIDATE_URL) || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true));
                $this->writeEnv([
                    'APP_ENV' => $local ? 'local' : 'production',
                    'APP_URL' => rtrim($url, '/'),
                    'APP_DEBUG' => $local ? 'true' : 'false',
                    'DEBUGBAR_ENABLED' => $local ? 'true' : 'false',
                    'TELESCOPE_ENABLED' => $local ? 'true' : 'false',
                    'ARTISAN_RUNNER_UI_ENABLED' => 'true',
                ]);
            }
            if (! config('app.key') && $this->confirm('Soll ein neuer Anwendungsschlüssel erzeugt werden?')) {
                $this->artisanStep('key:generate', ['--force' => true]);
            }
            if ($this->confirm('Soll die Datenbank konfiguriert werden?')) {
                $this->database();
            }
            $this->info('CORS betrifft Browserzugriffe von anderen Origins. Konfiguration: config/cors.php; Pfade: api/* und sanctum/csrf-cookie.');
            if ($this->confirm('Sollen erlaubte CORS-Origins konfiguriert werden?')) {
                do {
                    $origins = $this->ask('Origins durch Komma getrennt (leer = keine)', env('CORS_ALLOWED_ORIGINS', '')) ?? '';
                    $valid = true;
                    foreach (array_filter(array_map('trim', explode(',', $origins))) as $origin) {
                        if (! preg_match('~^https?://[^/\s?#]+$~', $origin) || ! filter_var($origin, FILTER_VALIDATE_URL)) {
                            $valid = false;
                        }
                    }
                    if (! $valid) {
                        $this->warn('Bitte vollständige Origins ohne Pfad oder Platzhalter angeben.');
                    }
                } while (! $valid);
                $this->writeEnv(['CORS_ALLOWED_ORIGINS' => $origins, 'CORS_SUPPORTS_CREDENTIALS' => $this->confirm('Sollen CORS-Anfragen Cookies/Anmeldedaten mitsenden dürfen?') ? 'true' : 'false']);
            }
            if ($this->confirm('Sollen offene Datenbank-Migrationen ausgeführt werden?')) {
                try {
                    DB::connection()->getPdo();
                    $connected = true;
                } catch (\Throwable) {
                    $connected = false;
                }
                if ($connected) {
                    $this->artisanStep('migrate', ['--force' => true]);
                } else {
                    $this->notes[] = 'Migrationen offen: Datenbank nicht erreichbar. Datenbankkonfiguration und PDO-Treiber prüfen.';
                    $this->warn(end($this->notes));
                }
            }
            if ($this->confirm('Soll ein Benutzer mit Filament- und Artisan-Runner-Rechten angelegt werden?')) {
                try {
                    DB::connection()->getPdo();
                    $ready = Schema::hasTable('users') && Schema::hasTable('permissions');
                } catch (\Throwable) {
                    $ready = false;
                }
                if ($ready) {
                    $email = $this->ask('E-Mail');
                    $this->artisanStep('app:create-user', ['email' => $email, '--admin' => true, '--runner' => true]);
                } else {
                    $this->warn('Benutzer übersprungen: Datenbank oder notwendige Tabellen fehlen.');
                    $this->notes[] = 'Benutzeranlage offen: zunächst Datenbank konfigurieren und migrieren.';
                }
            }
            if ($this->confirm('Sollen npm-Abhängigkeiten installiert werden?')) {
                $this->processStep(is_file(base_path('package-lock.json')) ? 'npm ci --ignore-scripts' : 'npm install --ignore-scripts');
            }
            if ($this->confirm('Sollen die Frontend-Assets gebaut werden?')) {
                if (is_dir(base_path('node_modules'))) {
                    $this->processStep('npm run build');
                } else {
                    $this->notes[] = 'Frontend-Build offen: zuerst npm-Abhängigkeiten installieren.';
                    $this->warn(end($this->notes));
                }
            }
            if ($this->confirm('Soll der öffentliche Storage-Link für Medien erstellt werden?')) {
                $this->artisanStep('storage:link');
            }
            $this->report();

            return self::SUCCESS;
        } catch (\Throwable $e) {
            // Database exceptions may include passwords or SQL. Do not print them.
            $this->error('Einrichtungsschritt fehlgeschlagen ('.class_basename($e).'). Einstellungen und Voraussetzungen prüfen und php setup.php erneut ausführen.');

            return self::FAILURE;
        }
    }

    private function database(): void
    {
        $driver = $this->choice('Datenbank', ['sqlite', 'mariadb', 'mysql'], 0);
        if ($driver === 'sqlite') {
            $path = database_path('database.sqlite');
            if (! is_file($path)) {
                if (! $this->confirm('Soll die SQLite-Datei erstellt werden?')) {
                    $this->warn('Datenbankkonfiguration übersprungen: SQLite-Datei fehlt.');

                    return;
                }
                if (! touch($path)) {
                    throw new RuntimeException('SQLite');
                }
            }
            $values = ['DB_CONNECTION' => $driver, 'DB_DATABASE' => $path, 'DB_URL' => '', 'DB_SOCKET' => ''];
        } else {
            $host = $this->ask('Datenbank-Host', '127.0.0.1');
            do {
                $port = (string) $this->ask('Port', '3306');
            } while (! ctype_digit($port) || (int) $port < 1 || (int) $port > 65535);
            do {
                $name = $this->ask('Datenbankname', 'laravel_webapp');
            } while (! preg_match('/^[a-zA-Z0-9_]+$/', $name));
            $user = $this->ask('Datenbank-Benutzer', 'laravel_webapp');
            $password = $this->secret('Datenbank-Passwort') ?? '';
            if ($this->confirm('Soll die Datenbank auf dem vorhandenen Server erstellt werden (Benutzer benötigt CREATE-Recht)?')) {
                $pdo = new \PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
            $values = ['DB_CONNECTION' => $driver, 'DB_HOST' => $host, 'DB_PORT' => $port, 'DB_DATABASE' => $name, 'DB_USERNAME' => $user, 'DB_PASSWORD' => $password, 'DB_CHARSET' => 'utf8mb4', 'DB_COLLATION' => 'utf8mb4_unicode_ci', 'DB_URL' => '', 'DB_SOCKET' => ''];
        }
        $this->writeEnv($values);
    }

    private function writeEnv(array $values): void
    {
        $path = base_path('.env');
        $text = file_get_contents($path);
        foreach ($values as $key => $value) {
            $line = $key.'='.SetupEnvironment::quote((string) $value);
            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
            $text = preg_match($pattern, $text) ? preg_replace_callback($pattern, fn () => $line, $text) : rtrim($text)."\n".$line."\n";
        }
        if (file_put_contents($path, $text, LOCK_EX) === false) {
            throw new RuntimeException('env');
        }
        $this->artisanStep('config:clear');
        // Refresh the existing config repository so bound services also see changes.
        foreach (array_keys($values) as $key) {
            Env::getRepository()->clear($key);
        }
        Dotenv::create(Env::getRepository(), base_path(), '.env')->load();
        foreach (glob(config_path('*.php')) as $file) {
            config()->set(pathinfo($file, PATHINFO_FILENAME), require $file);
        }
        app()->detectEnvironment(fn () => config('app.env'));
        DB::purge();
    }

    private function artisanStep(string $command, array $arguments = []): void
    {
        if ($this->call($command, $arguments) !== self::SUCCESS) {
            throw new RuntimeException($command);
        }
    }

    private function processStep(string $command): void
    {
        $result = Process::path(base_path())->forever()->run($command, function ($type, $output): void {
            $this->output->write($output);
        });
        if (! $result->successful()) {
            throw new RuntimeException('Frontend');
        }
    }

    private function report(): void
    {
        $url = rtrim(config('app.url'), '/');
        $lines = ['# Einrichtung und Start', '', 'Konfigurierte Basis-URL: '.$url, '', 'Die URLs sind nach dem Start des Servers bzw. Einrichtung des Webservers erreichbar. Anmeldung benötigt einen angelegten Benutzer.', '', '| Funktion | URL |', '|---|---|'];
        foreach (['Startseite' => '/', 'Anmeldung' => '/login', 'Dashboard' => '/dashboard', 'Filament' => '/admin', 'Health' => '/up'] as $name => $path) {
            $lines[] = '| '.$name.' | '.$url.$path.' |';
        }
        if (config('artisan-runner-ui.enabled') && in_array(app()->environment(), config('artisan-runner-ui.environments', []), true)) {
            $lines[] = '| Artisan Runner UI | '.$url.'/'.trim(config('artisan-runner-ui.path'), '/').' |';
        }
        if (app()->environment('local')) {
            if (class_exists(Telescope::class) && config('telescope.enabled')) {
                $lines[] = '| Telescope | '.$url.'/telescope |';
            }
            if (class_exists(LaravelBrainServiceProvider::class)) {
                $lines[] = '| Laravel Brain | '.$url.'/_laravel-brain |';
            }
        }
        $lines = array_merge($lines, ['', '## Startbefehle', '', '```sh', 'php artisan serve --host=127.0.0.1 --port=8000', 'php artisan queue:work', 'php artisan schedule:work', '```', '', 'Der lokale Server verwendet http://127.0.0.1:8000. Für andere Basis-URLs Host/Port bzw. Webserver passend konfigurieren. In Produktion den Webserver auf public/ richten, Queue als Dienst betreiben und php artisan schedule:run minütlich ausführen.']);
        if (is_dir(base_path('node_modules'))) {
            $lines[] = "\nFrontend-Entwicklung: `npm run dev`";
        }
        $lines = array_merge($lines, ['', 'CORS: config/cors.php; Origins in .env. Aktuell erfasste Pfade: api/* und sanctum/csrf-cookie. Kopia wird separat eingerichtet.'], $this->notes);
        $report = implode("\n", $lines)."\n";
        $this->line($report);
        if ($this->confirm('Soll diese Übersicht in SETUP.md geschrieben werden?')) {
            if (file_put_contents(base_path('SETUP.md'), $report) === false) {
                throw new RuntimeException('Bericht');
            }
        }
    }
}
