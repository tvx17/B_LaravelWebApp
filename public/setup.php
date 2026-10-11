<?php

// Standalone entry point: deliberately does not bootstrap Laravel before login.
declare(strict_types=1);
use App\Support\BrowserSetup;

ini_set('display_errors', '0');
if (PHP_VERSION_ID < 80300) {
    http_response_code(503);
    exit('PHP 8.3 oder neuer erforderlich.');
}
$root = dirname(__DIR__);
$configuration = $root.'/web-setup.local.php';
$completed = $root.'/storage/app/web-setup.complete';
if (is_file($completed) || ! is_file($configuration)) {
    http_response_code(404);
    exit('Browser-Einrichtung nicht aktiviert oder bereits abgeschlossen. Anleitung: docs/browser-setup.md');
}
$settings = require $configuration;
if (! is_array($settings) || ! is_string($settings['access_token'] ?? null) || strlen($settings['access_token']) < 32) {
    http_response_code(503);
    exit('In web-setup.local.php einen privaten Zugangscode mit mindestens 32 Zeichen hinterlegen.');
}
$https = ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
if (! $https && ! in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('Bitte den Einrichtungsassistenten über HTTPS öffnen.');
}
$nonce = base64_encode(random_bytes(18));
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'none'; style-src 'nonce-{$nonce}'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
session_name('laravel_browser_setup');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly' => true, 'secure' => $https, 'samesite' => 'Strict', 'path' => '/']);
if (! session_start()) {
    http_response_code(503);
    exit('PHP-Sessions nicht verfügbar. Hosting-Konfiguration prüfen.');
}
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$_SESSION['step'] ??= 0;
$_SESSION['results'] ??= [];
$authenticated = isset($_SESSION['authenticated_at']) && $_SESSION['authenticated_at'] > time() - 3600
    && hash_equals(hash('sha256', $settings['access_token']), $_SESSION['token_hash'] ?? '');
if (! $authenticated) {
    $_SESSION['step'] = 0;
    $_SESSION['results'] = [];
}
$error = null;
$finished = false;
require_once $root.'/app/Support/BrowserSetup.php';
$steps = BrowserSetup::STEPS;
$ids = array_keys($steps);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! is_string($_POST['csrf'] ?? null) || ! hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        http_response_code(403);
        exit('Ungültige Anfrage. Seite neu laden.');
    }
    if (! $authenticated) {
        if (is_string($_POST['access_token'] ?? null) && hash_equals($settings['access_token'], $_POST['access_token'])) {
            session_regenerate_id(true);
            $_SESSION['authenticated_at'] = time();
            $_SESSION['token_hash'] = hash('sha256', $settings['access_token']);
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            header('Location: setup.php', true, 303);
            exit;
        }
        $error = 'Zugangscode ungültig.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'back') {
            $_SESSION['step'] = max(0, $_SESSION['step'] - 1);
        } elseif ($action === 'skip' && $ids[$_SESSION['step']] !== 'finish') {
            $_SESSION['results'][$ids[$_SESSION['step']]] = 'Übersprungen';
            $_SESSION['step']++;
        } elseif ($action === 'run') {
            $lock = fopen($root.'/storage/framework/web-setup.lock', 'c');
            if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
                $error = 'Eine andere Einrichtung läuft oder Schreibrechte fehlen. Später erneut versuchen.';
            } else {
                try {
                    if (is_file($completed)) {
                        throw new RuntimeException('Einrichtung wurde bereits abgeschlossen.');
                    }
                    $input = array_filter($_POST, fn ($value) => is_string($value));
                    $step = $ids[$_SESSION['step']];
                    if (in_array($input['environment'] ?? '', ['local', 'production'], true)) {
                        $_SESSION['environment'] = $input['environment'];
                    }
                    $input['environment'] = $_SESSION['environment'] ?? 'production';
                    $input['history'] = $_SESSION['results'];
                    $engine = new BrowserSetup($root, $settings);
                    $message = $engine->run($step, $input);
                    if ($step === 'finish') {
                        $finished = true;
                        $report = $message;
                        $_SESSION = [];
                        session_destroy();
                    } else {
                        $_SESSION['results'][$step] = $message;
                        $_SESSION['step']++;
                    }
                } catch (Throwable $exception) {
                    // Never display database exceptions, SQL, process logs, or credentials.
                    $error = get_class($exception) === RuntimeException::class
                        ? $exception->getMessage()
                        : 'Schritt fehlgeschlagen. Voraussetzungen, Datenbankzugang und Schreibrechte prüfen. Passwörter bitte erneut eingeben.';
                } finally {
                    flock($lock, LOCK_UN);
                }
            }
            if (is_resource($lock)) {
                fclose($lock);
            }
        }
        if (! $error && ! $finished) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            header('Location: setup.php', true, 303);
            exit;
        }
    }
}
function setupEscape(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
$index = min((int) ($_SESSION['step'] ?? 0), count($ids) - 1);
$step = $ids[$index];
$processes = ! empty($settings['allow_processes']) && function_exists('proc_open');
$mode = $_SESSION['environment'] ?? 'production';
header('Content-Type: text/html; charset=UTF-8');
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel · Einrichtung</title>
    <style nonce="<?= setupEscape($nonce) ?>">
        :root{color-scheme:light;font-family:system-ui,sans-serif;color:#17212f;background:#f1f4f8}*{box-sizing:border-box}body{margin:0}main{max-width:980px;margin:48px auto;padding:0 20px}.brand{font-size:13px;text-transform:uppercase;letter-spacing:.16em;color:#57657a}h1{font-size:32px;margin:12px 0 8px}h2{margin-top:0;font-size:23px}p{line-height:1.6;color:#546174}.layout{display:grid;grid-template-columns:230px 1fr;gap:24px;margin-top:28px}.card{background:white;border:1px solid #dce3eb;border-radius:16px;padding:28px;box-shadow:0 8px 25px #17212f06}nav ol{padding:0;list-style:none;margin:0}nav li{padding:10px 12px;margin:4px 0;font-size:14px;border-radius:8px;color:#64748b}nav li.current{background:#e7efff;color:#1747b6;font-weight:700}.done{color:#1c734b}.status{font-size:11px;display:block;margin-top:3px}label{display:block;font-weight:600;font-size:14px;margin:18px 0 7px}input:not([type=checkbox]),select{width:100%;border:1px solid #bbc7d6;border-radius:8px;padding:12px;font:inherit;background:white;color:inherit}input:focus,select:focus,button:focus-visible{outline:3px solid #a5bfff;outline-offset:2px}.check{display:flex;align-items:flex-start;gap:10px;font-weight:400;line-height:1.5}.check input{margin-top:5px}.buttons{display:flex;gap:10px;flex-wrap:wrap;margin-top:26px}button{font:inherit;font-size:14px;cursor:pointer;padding:12px 16px;border-radius:8px;border:1px solid #ccd5e2;background:white;color:#334155}.primary{background:#245ad3;color:white;border-color:#245ad3}button:disabled{opacity:.5;cursor:not-allowed}.notice,.error{padding:14px 16px;border-radius:10px;font-size:14px;line-height:1.6;background:#eef4ff;color:#284a87;margin:18px 0}.error{background:#fff0f0;color:#9b2929}.muted{font-size:13px}pre{white-space:pre-wrap;word-break:break-word;font-size:13px;line-height:1.65}progress{width:100%;height:8px;accent-color:#245ad3;margin:16px 0}code{background:#edf1f6;padding:2px 5px;border-radius:4px}footer{margin:22px 0;color:#64748b;font-size:12px}@media(max-width:700px){main{margin:24px auto}.layout{grid-template-columns:1fr}nav ol{display:flex;flex-wrap:wrap}nav li{font-size:12px;padding:5px 8px}.status{display:none}.card{padding:22px}h1{font-size:27px}}
    </style>
</head>
<body><main>
    <div class="brand">Laravel Web App</div>
    <h1><?= $finished ? 'Einrichtung abgeschlossen' : 'Schritt für Schritt startklar' ?></h1>
    <p>Du entscheidest bei jedem Schritt. Vorhandene Daten und Anwendungsschlüssel bleiben erhalten.</p>
    <?php if ($error) { ?><div class="error" role="alert"><?= setupEscape($error) ?></div><?php } ?>
    <?php if ($finished) { ?>
        <section class="card"><p>Der Assistent ist jetzt gesperrt. Entferne <code>public/setup.php</code> und <code>web-setup.local.php</code> über die Dateiverwaltung.</p><pre><?= setupEscape($report) ?></pre></section>
    <?php } elseif (! $authenticated) { ?>
        <section class="card"><h2>Geschützte Einrichtung</h2><p>Gib den Zugangscode aus deiner <code>web-setup.local.php</code> ein. Er gehört nicht in die URL.</p>
            <form method="post" action="setup.php"><input type="hidden" name="csrf" value="<?= setupEscape($_SESSION['csrf']) ?>"><label for="access_token">Zugangscode</label><input type="password" id="access_token" name="access_token" required autocomplete="off" minlength="32"><div class="buttons"><button class="primary">Assistent öffnen</button></div></form>
        </section>
    <?php } else { ?>
        <progress value="<?= $index ?>" max="<?= count($ids) ?>" aria-label="Einrichtungsfortschritt"></progress>
        <div class="layout"><nav aria-label="Einrichtungsschritte"><ol>
            <?php foreach ($steps as $id => $title) { ?><li class="<?= $id === $step ? 'current' : '' ?>" <?= $id === $step ? 'aria-current="step"' : '' ?>><?= setupEscape($title) ?><?php if (isset($_SESSION['results'][$id])) { ?><span class="status"><?= $_SESSION['results'][$id] === 'Übersprungen' ? 'Übersprungen' : 'Erledigt' ?></span><?php } ?></li><?php } ?>
        </ol></nav><section class="card"><div class="brand">Schritt <?= $index + 1 ?> von <?= count($ids) ?></div><h2><?= setupEscape($steps[$step]) ?></h2>
        <?php if ($index > 0 && isset($_SESSION['results'][$ids[$index - 1]])) { ?><div class="notice" role="status"><?= setupEscape($_SESSION['results'][$ids[$index - 1]]) ?></div><?php } ?>
        <form method="post" action="setup.php" autocomplete="off"><input type="hidden" name="csrf" value="<?= setupEscape($_SESSION['csrf']) ?>">
        <?php if ($step === 'env') { ?>
            <p>Soll die <code>.env</code> aus der Projektvorlage erstellt werden? Eine vorhandene Datei wird weiterverwendet.</p>
        <?php } elseif ($step === 'composer') { ?>
            <p>Sollen die PHP-Abhängigkeiten installiert werden? In Produktion werden Entwicklungswerkzeuge ausgelassen.</p>
            <label for="environment">Zielumgebung</label><select id="environment" name="environment"><option value="production" <?= $mode === 'production' ? 'selected' : '' ?>>Produktion</option><option value="local" <?= $mode === 'local' ? 'selected' : '' ?>>Entwicklung</option></select>
        <?php } elseif ($step === 'application') { ?>
            <p>Sollen Umgebung und Basis-URL eingestellt werden? Entwicklung aktiviert die lokalen Debug-Werkzeuge.</p>
            <label for="environment">Umgebung</label><select id="environment" name="environment"><option value="production" <?= $mode === 'production' ? 'selected' : '' ?>>Produktion</option><option value="local" <?= $mode === 'local' ? 'selected' : '' ?>>Entwicklung</option></select>
            <label for="url">Basis-URL der Anwendung</label><input type="url" id="url" name="url" placeholder="https://example.com" required>
        <?php } elseif ($step === 'key') { ?>
            <p>Soll ein Anwendungsschlüssel erzeugt werden? Ein vorhandener Schlüssel wird automatisch erhalten.</p>
        <?php } elseif ($step === 'database') { ?>
            <p>Soll die Datenbank konfiguriert werden? Für SQLite werden nur die Auswahl und die Bestätigung zur Dateierstellung benötigt.</p>
            <label for="driver">Datenbank</label><select id="driver" name="driver"><option value="sqlite">SQLite</option><option value="mariadb">MariaDB</option><option value="mysql">MySQL</option></select>
            <label class="check"><input type="checkbox" name="create_database" value="1">SQLite-Datei bzw. Serverdatenbank bei Bedarf erstellen. Bei MariaDB/MySQL benötigt der Benutzer CREATE-Rechte.</label>
            <details><summary>Zugangsdaten für MariaDB / MySQL</summary>
                <label for="host">Host</label><input id="host" name="host" value="127.0.0.1">
                <label for="port">Port</label><input id="port" name="port" type="number" value="3306" min="1" max="65535">
                <label for="database">Datenbankname</label><input id="database" name="database" value="laravel_webapp">
                <label for="username">Datenbank-Benutzer</label><input id="username" name="username" autocomplete="off">
                <label for="password">Datenbank-Passwort</label><input id="password" name="password" type="password" autocomplete="new-password">
            </details>
        <?php } elseif ($step === 'cors') { ?>
            <p>Sollen andere Browser-Origins erlaubt werden? Leer bedeutet keine. Die Konfiguration gilt für <code>api/*</code> und <code>sanctum/csrf-cookie</code>.</p>
            <label for="origins">Erlaubte Origins, durch Komma getrennt</label><input id="origins" name="origins" placeholder="https://frontend.example.com, https://app.example.com">
            <label class="check"><input type="checkbox" name="credentials" value="1">Cookies und Anmeldedaten bei CORS-Anfragen erlauben</label>
        <?php } elseif ($step === 'migrate') { ?>
            <p>Sollen die offenen Migrationen auf der konfigurierten Datenbank ausgeführt werden? Vorhandene Tabellen werden nicht zurückgesetzt. Bitte die zuvor gewählte Datenbank prüfen.</p>
        <?php } elseif ($step === 'user') { ?>
            <p>Soll ein Benutzer mit Zugriff auf Filament und die Artisan Runner UI angelegt werden? Die Runner UI ist nur lokal verfügbar. Vorhandene Benutzer werden nicht überschrieben.</p>
            <label for="name">Name</label><input id="name" name="name" required maxlength="255">
            <label for="email">E-Mail</label><input id="email" name="email" type="email" required maxlength="255">
            <label for="password">Passwort, mindestens 12 Zeichen</label><input id="password" name="password" type="password" required minlength="12" autocomplete="new-password">
            <label for="password_confirmation">Passwort wiederholen</label><input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password">
        <?php } elseif ($step === 'npm') { ?>
            <p>Sollen die Frontend-Abhängigkeiten mit npm installiert werden?</p>
        <?php } elseif ($step === 'build') { ?>
            <p>Sollen die Frontend-Dateien für den Betrieb gebaut werden? Alternativ kannst du das lokal erzeugte <code>public/build/</code> hochladen.</p>
        <?php } elseif ($step === 'storage') { ?>
            <p>Soll ein öffentlicher Link zum Medien-Verzeichnis angelegt werden? Dein Hosting muss symbolische Links unterstützen.</p>
        <?php } elseif ($step === 'finish') { ?>
            <p>Die Übersicht enthält die eingerichteten URLs und Betriebsbefehle. Mit dem Abschluss wird dieser Assistent gesperrt.</p>
            <label class="check"><input type="checkbox" name="save_report" value="1">Übersicht zusätzlich als SETUP.md speichern</label>
        <?php } ?>
        <?php if (in_array($step, ['composer', 'npm', 'build'], true) && ! $processes) { ?><div class="notice">Dein Hosting erlaubt hier keine freigegebene Prozessausführung. Bereite <code>vendor/</code> und <code>public/build/</code> lokal vor und lade sie hoch. Diesen Schritt anschließend überspringen.</div><?php } ?>
        <div class="buttons">
            <button class="primary" name="action" value="run" <?= in_array($step, ['composer', 'npm', 'build'], true) && ! $processes ? 'disabled' : '' ?>><?= $step === 'finish' ? 'Ja, abschließen und sperren' : 'Ja, Schritt durchführen' ?></button>
            <?php if ($step !== 'finish') { ?><button name="action" value="skip" formnovalidate>Nein, überspringen</button><?php } ?>
            <?php if ($index > 0) { ?><button name="action" value="back" formnovalidate>Zurück</button><?php } ?>
        </div></form>
        <p class="muted">Bei Fehlern bleibt dieser Schritt geöffnet. Passwörter werden weder angezeigt noch in der Sitzung gespeichert.</p>
        </section></div>
    <?php } ?>
    <footer>Geschützte Einrichtung · Keine dauerhaften Dienste werden gestartet · Kopia bleibt separat</footer>
</main></body></html>
