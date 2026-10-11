<?php

namespace App\Support;

use App\Models\User;
use Dotenv\Dotenv;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Symfony\Component\Console\Output\BufferedOutput;

/** Runs only fixed setup actions, also before Composer's autoloader exists. */
class BrowserSetup
{
    private ?Application $app = null;

    public const STEPS = [
        'env' => '.env erstellen',
        'composer' => 'PHP-Abhängigkeiten',
        'application' => 'Anwendung konfigurieren',
        'key' => 'Anwendungsschlüssel',
        'database' => 'Datenbank',
        'cors' => 'CORS',
        'migrate' => 'Migrationen',
        'user' => 'Administrator',
        'npm' => 'Frontend-Abhängigkeiten',
        'build' => 'Frontend bauen',
        'storage' => 'Medien-Verzeichnis',
        'finish' => 'Abschluss',
    ];

    public function __construct(private string $root, private array $settings) {}

    public function run(string $step, array $input): string
    {
        if (! array_key_exists($step, self::STEPS)) {
            throw new RuntimeException('Unbekannter Schritt.');
        }
        switch ($step) {
            case 'env':
                if (is_file($this->root.'/.env')) {
                    return 'Vorhandene .env bleibt erhalten.';
                }
                $this->checked(file_put_contents($this->root.'/.env', file_get_contents($this->root.'/.env.example'), LOCK_EX));
                if (PHP_OS_FAMILY !== 'Windows') {
                    chmod($this->root.'/.env', 0600);
                }

                return '.env aus der Projektvorlage erstellt.';
            case 'composer':
                $mode = $input['environment'] ?? 'production';
                $this->process('composer', array_merge(['install', '--no-interaction'], $mode === 'local' ? [] : ['--no-dev', '--optimize-autoloader']));

                return 'Composer-Abhängigkeiten und Filament-Assets installiert.';
            case 'application':
                $mode = $input['environment'] ?? '';
                $url = rtrim($input['url'] ?? '', '/');
                if (! in_array($mode, ['local', 'production'], true) || ! filter_var($url, FILTER_VALIDATE_URL) || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true) || parse_url($url, PHP_URL_USER) !== null || parse_url($url, PHP_URL_QUERY) !== null || parse_url($url, PHP_URL_FRAGMENT) !== null) {
                    throw new RuntimeException('Bitte eine gültige Umgebung und eine HTTP(S)-Basis-URL ohne Zugangsdaten, Query oder Fragment angeben.');
                }
                $local = $mode === 'local' ? 'true' : 'false';
                $this->writeEnv(['APP_ENV' => $mode, 'APP_URL' => $url, 'APP_DEBUG' => $local, 'DEBUGBAR_ENABLED' => $local, 'TELESCOPE_ENABLED' => $local, 'ARTISAN_RUNNER_UI_ENABLED' => 'true']);

                return 'Anwendung konfiguriert. Die Runner UI bleibt auf die lokale Umgebung beschränkt.';
            case 'key':
                $env = $this->environment();
                if (! empty($env['APP_KEY'])) {
                    return 'Vorhandener Anwendungsschlüssel bleibt erhalten.';
                }
                $this->writeEnv(['APP_KEY' => 'base64:'.base64_encode(random_bytes(32))]);

                return 'Anwendungsschlüssel erzeugt.';
            case 'database':
                return $this->database($input);
            case 'cors':
                $origins = array_values(array_filter(array_map('trim', explode(',', $input['origins'] ?? ''))));
                foreach ($origins as $origin) {
                    if (! preg_match('~^https?://[^/\s?#]+$~', $origin) || ! filter_var($origin, FILTER_VALIDATE_URL) || parse_url($origin, PHP_URL_USER) !== null) {
                        throw new RuntimeException('Origins müssen aus Schema, Host und optional Port bestehen, ohne Pfad oder Platzhalter.');
                    }
                }
                $this->writeEnv(['CORS_ALLOWED_ORIGINS' => implode(',', $origins), 'CORS_SUPPORTS_CREDENTIALS' => isset($input['credentials']) ? 'true' : 'false']);

                return 'CORS konfiguriert für api/* und sanctum/csrf-cookie. Dadurch werden keine API-Endpunkte erstellt.';
            case 'migrate':
                $this->artisan('migrate', ['--force' => true]);

                return 'Offene Migrationen ausgeführt.';
            case 'user':
                $this->user($input);

                return 'Benutzer mit Filament- und Artisan-Runner-Berechtigung angelegt.';
            case 'npm':
                $this->process('npm', [is_file($this->root.'/package-lock.json') ? 'ci' : 'install', '--ignore-scripts']);

                return 'Frontend-Abhängigkeiten installiert.';
            case 'build':
                if (! is_dir($this->root.'/node_modules')) {
                    throw new RuntimeException('Zuerst npm-Abhängigkeiten installieren oder lokal gebaute public/build-Dateien hochladen.');
                }
                $this->process('npm', ['run', 'build']);

                return 'Frontend gebaut.';
            case 'storage':
                $this->artisan('storage:link');

                return 'Öffentlicher Storage-Link eingerichtet.';
            case 'finish':
                $report = $this->report();
                $report .= "\n## Durchgeführte Schritte\n\n";
                foreach (($input['history'] ?? []) as $id => $result) {
                    if (isset(self::STEPS[$id])) {
                        $report .= '- '.self::STEPS[$id].': '.$result."\n";
                    }
                }
                if (isset($input['save_report'])) {
                    $this->checked(file_put_contents($this->root.'/SETUP.md', $report, LOCK_EX));
                }
                $this->checked(file_put_contents($this->root.'/storage/app/web-setup.complete', date(DATE_ATOM), LOCK_EX));

                return $report;
        }

        throw new RuntimeException('Schritt nicht verfügbar.');
    }

    private function dependencies(): void
    {
        if (! is_file($this->root.'/vendor/autoload.php')) {
            throw new RuntimeException('vendor/ fehlt. Composer-Schritt ausführen oder lokal installierte Abhängigkeiten hochladen.');
        }
        require_once $this->root.'/vendor/autoload.php';
    }

    private function environment(): array
    {
        $this->dependencies();
        if (! is_file($this->root.'/.env')) {
            throw new RuntimeException('Zuerst .env erstellen.');
        }

        return Dotenv::parse(file_get_contents($this->root.'/.env'));
    }

    private function writeEnv(array $values): void
    {
        $this->environment();
        $path = $this->root.'/.env';
        $text = file_get_contents($path);
        foreach ($values as $key => $value) {
            $line = $key.'='.SetupEnvironment::quote((string) $value);
            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
            $text = preg_match($pattern, $text) ? preg_replace_callback($pattern, fn () => $line, $text) : rtrim($text)."\n".$line."\n";
        }
        $this->checked(file_put_contents($path, $text, LOCK_EX));
        $cache = $this->root.'/bootstrap/cache/config.php';
        if (is_file($cache) && ! unlink($cache)) {
            throw new RuntimeException('Konfigurationscache konnte nicht geleert werden. Schreibrechte prüfen.');
        }
    }

    private function database(array $input): string
    {
        $driver = $input['driver'] ?? '';
        if (! in_array($driver, ['sqlite', 'mariadb', 'mysql'], true)) {
            throw new RuntimeException('Unbekannte Datenbank.');
        }
        $this->environment();
        if ($driver === 'sqlite') {
            if (! extension_loaded('pdo_sqlite')) {
                throw new RuntimeException('PHP-Erweiterung pdo_sqlite fehlt.');
            }
            $path = $this->root.'/database/database.sqlite';
            if (! is_file($path)) {
                if (! isset($input['create_database'])) {
                    throw new RuntimeException('SQLite-Datei fehlt. Erstellung bestätigen oder Datei hochladen.');
                }
                $this->checked(touch($path));
            }
            $values = ['DB_CONNECTION' => $driver, 'DB_DATABASE' => $path, 'DB_URL' => '', 'DB_SOCKET' => ''];
        } else {
            $host = $input['host'] ?? '';
            $port = $input['port'] ?? '';
            $name = $input['database'] ?? '';
            if (! preg_match('/^[a-zA-Z0-9_.:\-]+$/', $host) || ! ctype_digit($port) || (int) $port < 1 || (int) $port > 65535 || ! preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
                throw new RuntimeException('Host, Port oder Datenbankname ungültig.');
            }
            if (! extension_loaded('pdo_mysql')) {
                throw new RuntimeException('PHP-Erweiterung pdo_mysql fehlt.');
            }
            $user = $input['username'] ?? '';
            $password = $input['password'] ?? '';
            try {
                $pdo = new \PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
                if (isset($input['create_database'])) {
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                }
                $pdo->exec("USE `{$name}`");
            } catch (\Throwable) {
                throw new RuntimeException('Datenbankzugriff fehlgeschlagen. Zugangsdaten, Server und gegebenenfalls CREATE-Recht prüfen.');
            }
            $values = ['DB_CONNECTION' => $driver, 'DB_HOST' => $host, 'DB_PORT' => $port, 'DB_DATABASE' => $name, 'DB_USERNAME' => $user, 'DB_PASSWORD' => $password, 'DB_CHARSET' => 'utf8mb4', 'DB_COLLATION' => 'utf8mb4_unicode_ci', 'DB_URL' => '', 'DB_SOCKET' => ''];
        }
        $this->writeEnv($values);

        return 'Datenbank konfiguriert. Vorhandene Tabellen bleiben erhalten.';
    }

    private function application(): Application
    {
        if ($this->app !== null) {
            return $this->app;
        }
        $this->environment();
        // Each web request creates a fresh application using the edited .env.
        $app = require $this->root.'/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $this->app = $app;
    }

    private function artisan(string $command, array $arguments = []): void
    {
        $app = $this->application();
        $output = new BufferedOutput;
        $code = $app->make(Kernel::class)->call($command, $arguments, $output);
        if ($code !== 0) {
            throw new RuntimeException('Schritt fehlgeschlagen. Voraussetzungen und Schreibrechte prüfen.');
        }
    }

    private function user(array $input): void
    {
        $this->application();
        $data = array_intersect_key($input, array_flip(['name', 'email', 'password', 'password_confirmation']));
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(12)],
        ]);
        if ($validator->fails()) {
            throw new RuntimeException('Name/E-Mail prüfen; E-Mail muss neu sein. Passwort mindestens 12 Zeichen, Wiederholung identisch.');
        }
        DB::transaction(function () use ($data): void {
            $user = User::create(array_intersect_key($data, array_flip(['name', 'email', 'password'])));
            foreach (['access-admin', 'run-artisan-runner-ui'] as $permission) {
                $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
            }
        });
    }

    private function process(string $program, array $arguments): void
    {
        if (empty($this->settings['allow_processes']) || ! function_exists('proc_open')) {
            throw new RuntimeException('Prozessausführung nicht freigegeben/verfügbar. vendor/ und fertig gebaute Assets lokal vorbereiten und hochladen.');
        }
        $prefix = $this->settings[$program] ?? [];
        if (! is_array($prefix) || $prefix === []) {
            throw new RuntimeException('Programm in web-setup.local.php konfigurieren.');
        }
        // Fixed program/arguments; no command text comes from the browser.
        $command = array_merge($prefix, $arguments);
        $log = tmpfile();
        if ($log === false) {
            throw new RuntimeException('Temporäre Datei nicht verfügbar.');
        }
        try {
            $process = proc_open($command, [0 => ['pipe', 'r'], 1 => $log, 2 => $log], $pipes, $this->root, null, ['bypass_shell' => true]);
            if (! is_resource($process)) {
                throw new RuntimeException('Programm konnte nicht gestartet werden.');
            }
            fclose($pipes[0]);
            $started = microtime(true);
            do {
                $status = proc_get_status($process);
                if (! $status['running']) {
                    break;
                }
                if (microtime(true) - $started > 90) {
                    proc_terminate($process);
                    proc_close($process);
                    throw new RuntimeException('Hosting-Zeitlimit erreicht. Abhängigkeiten/Assets lokal vorbereiten und hochladen.');
                }
                usleep(100000);
            } while (true);
            $code = proc_close($process);
            if (($code === -1 ? $status['exitcode'] : $code) !== 0) {
                throw new RuntimeException('Programm fehlgeschlagen. Programmpfad, Netzwerk und Hosting-Limits prüfen. Alternativ Abhängigkeiten/Assets lokal vorbereiten und hochladen.');
            }
        } finally {
            fclose($log);
        }
    }

    public function report(): string
    {
        if (! is_file($this->root.'/vendor/autoload.php') || ! is_file($this->root.'/.env')) {
            return "# Einrichtung und Start\n\nOffen: vendor/ oder .env fehlt. Die Anwendung ist noch nicht lauffähig. Abhängigkeiten lokal installieren und hochladen; die .env aus der Vorlage erstellen.\n";
        }
        $app = $this->application();
        $url = rtrim($app['config']->get('app.url'), '/');
        $routes = $app['router']->getRoutes();
        $lines = ['# Einrichtung und Start', '', '| Funktion | URL |', '|---|---|'];
        foreach (['Startseite' => '/', 'Anmeldung' => '/login', 'Dashboard' => '/dashboard', 'Filament' => '/admin', 'Health' => '/up', 'Artisan Runner UI' => '/'.trim($app['config']->get('artisan-runner-ui.path', 'artisan-runner-ui'), '/'), 'Telescope' => '/telescope', 'Laravel Brain' => '/_laravel-brain'] as $label => $path) {
            foreach ($routes as $route) {
                if (trim($route->uri(), '/') === trim($path, '/') && in_array('GET', $route->methods(), true)) {
                    $lines[] = '| '.$label.' | '.$url.$path.' |';
                    break;
                }
            }
        }
        $lines = array_merge($lines, ['', '## Betrieb', '', 'Webserver-Dokumentwurzel: public/. Die URL-Erreichbarkeit hängt von Webserver, DNS und HTTPS ab.', '', 'Bei Shell-Zugriff (separate Prozesse):', '', '```sh', 'php artisan serve --host=127.0.0.1 --port=8000', 'php artisan queue:work', 'php artisan schedule:work', '```', '', 'Ohne Shell: Scheduler als Hosting-Cronjob mit php artisan schedule:run einmal pro Minute einrichten. Queue-Worker über den Hosting-Anbieter bereitstellen. Der Browser-Assistent startet keine dauerhaften Dienste.', '', 'CORS: config/cors.php, Origins in .env. Kopia bleibt separat.']);
        if (! is_file($this->root.'/public/build/manifest.json')) {
            $lines[] = "\nOffen: Frontend-Assets fehlen. Lokal npm ci --ignore-scripts und npm run build ausführen und public/build hochladen.";
        }
        if (! $app['config']->get('app.key')) {
            $lines[] = "\nOffen: Anwendungsschlüssel fehlt.";
        }
        $lines[] = "\nDer Browser-Assistent ist nach Abschluss gesperrt. public/setup.php und web-setup.local.php anschließend per Dateiverwaltung entfernen.";

        return implode("\n", $lines)."\n";
    }

    private function checked(mixed $result): void
    {
        if ($result === false) {
            throw new RuntimeException('Datei konnte nicht geschrieben werden. Schreibrechte prüfen.');
        }
    }
}
