# Laravel-Installation: Einstellungen, Erweiterungen und Aufrufe

Stand: **2026-10-08**, Laravel **13.35.0**, PHP **8.5.6**, Windows, Blade-Frontend. Diese Übersicht beschreibt die tatsächlich vorhandene Installation. Passwörter, Schlüssel und OAuth-Zugangsdaten werden nicht dokumentiert.

## Vollständige Referenzen

- [Alle effektiven Konfigurationswerte](konfigurationsreferenz.md): 31 Bereiche mit lokalen Werten und zusammengeführten Paketdefaults.
- [Alle PHP- und JavaScript-Abhängigkeiten](abhaengigkeiten.md): vollständige Lockfile-Versionen einschließlich transitiver Bibliotheken.
- [Alle verfügbaren Artisan-Befehle](artisan-befehle.md): Befehlsnamen und Beschreibungen der lokalen Installation.
- [Projektlog und offene Aufgaben](../PROJEKTLOG.md): Einrichtungshistorie und noch notwendige Schritte.

Die Referenzen sind eine Momentaufnahme. Nach Änderungen an `.env`, Konfiguration oder Lockfiles müssen sie nachgeführt werden. `.env.example` enthält Vorlagewerte; sie können von der lokalen `.env` abweichen.

## Laravel-Grundeinstellungen

| Bereich | Aktuelle Einstellung | Quelle / Wirkung |
|---|---|---|
| Anwendung | Name `Laravel`, Umgebung `local`, Debug aktiv | `config/app.php`; Debugbar nur lokal |
| Basis-URL | `http://localhost:8000` | `APP_URL`; ersetzt nicht den Serverstart |
| Sprache | `en`, Fallback `en`, Faker `en_US` | Oberfläche enthält eigene deutsche Blade-Texte |
| Zeitzone | `UTC` | `config/app.php`; unabhängig von der Windows-Zeitzone |
| Verschlüsselung | `AES-256-CBC`, lokaler App-Schlüssel vorhanden | Schlüssel nicht veröffentlichen oder für bestehende Daten unbedacht erneuern |
| Wartungsmodus | Dateitreiber | `php artisan down`, anschließend `php artisan up` |
| Datenbank | SQLite, `database/database.sqlite` | MariaDB vorbereitet, noch nicht aktiv |
| Anmeldung | User-Modell, Session-Guard `web`, E-Mail / Passwort | Fortify; Benutzeranlage interaktiv |
| Sessions | Datenbank, 120 Minuten, nicht zusätzlich verschlüsselt, Pfad `/` | `config/session.php`; weitere Cookie-Einstellungen in Referenz |
| Cache | Datenbank | `config/cache.php` |
| Queue | Datenbank, Standardqueue `default`, Retry nach 90 Sekunden | Worker separat starten |
| Dateien | `local`: `storage/app/private`; `public`: `storage/app/public` | `config/filesystems.php` |
| Medien | privater lokaler Datenträger, maximal 10 MiB, Bildtreiber GD | Media Library; Konvertierungen standardmäßig in der Queue |
| Mail | `log` | Mails werden protokolliert; kein tatsächlicher Mailversand eingerichtet |
| Logging | Stack mit `single`, Level `debug` | `storage/logs/laravel.log` |
| Broadcasting | Treiber `log` aus Framework-Konfiguration | Keine eigene `config/broadcasting.php` oder fachliche Broadcast-Funktion vorhanden |
| Redis | Client `phpredis`, Standardadresse `127.0.0.1:6379` vorbereitet | Aktuelle Queue, Sessions und Cache benötigen Redis nicht |
| S3 / externe Dienste | Konfigurationsplätze vorhanden | Keine dadurch bereits eingerichtete Integration; siehe maskierte Referenz |

Nach Konfigurationsänderungen `php artisan config:clear` ausführen. Für Deployments Konfiguration und Umgebung gesondert prüfen; insbesondere `APP_DEBUG=false`, HTTPS, Cookies, Datenbank und Secrets. `php artisan config:cache` erst mit der vorgesehenen Zielkonfiguration ausführen.

## Start, Entwicklung und Wartung

Alle Befehle im Projektstamm ausführen:

```powershell
composer install
npm install --ignore-scripts
php artisan serve --host=127.0.0.1 --port=8000
```

In einem zweiten Terminal `npm run dev` starten. Alternativ `npm run build` für fertige Assets. Vite verarbeitet `resources/css/app.css` und `resources/js/app.js`; Tailwind 4.3.3, Vite 8.3.3 und Laravel-Vite-Plugin 3.2.0 sind eingerichtet. Instrument Sans wird über die Font-Integration eingebunden. `concurrently` und optional `@laravel/multiplex` unterstützen Entwicklungsprozesse, sind keine Anwendungs-Webseiten.

| Befehl | Zweck |
|---|---|
| `composer dev` / `php artisan dev` | Registrierte Entwicklungsprozesse starten; `php artisan dev:list` zeigt sie |
| `php artisan migrate:status` | Migrationsstatus prüfen |
| `php artisan migrate` | Noch offene Migrationen auf der konfigurierten Datenbank anwenden |
| `php artisan queue:work --queue=default` | Datenbankjobs und Medienkonvertierungen verarbeiten |
| `php artisan schedule:work` | Lokal den Scheduler ausführen |
| `php artisan storage:link` | Nur bei Bedarf den öffentlichen Datenträger über `public/storage` erreichbar machen |
| `php artisan optimize:clear` | Laravel-Konfigurations-, Routen- und weitere Framework-Caches leeren |
| `composer audit` | Sicherheitsmeldungen zu Composer-Abhängigkeiten prüfen |
| `.\Resume-Codex.ps1` | Gespeicherte Codex-Projektsitzung wieder aufnehmen |

`php setup.php` startet den [interaktiven Einrichtungsassistenten](setup.md), alternativ `composer setup`. Jeder Schritt benötigt eine Bestätigung. Vorhandene Anwendungsschlüssel bleiben erhalten; Datenbank, CORS, Migrationen, Benutzer und Assets werden auf Wunsch eingerichtet.

Seit 2026-10-11 entspricht die Setup-Struktur dem Projekt LaravelApi: eigenständige CLI-Schritte, mehrsprachiger Browser-Assistent unter `/setup.php`, `.setup-access` zur ausdrücklichen Freischaltung und Fortschrittsanzeige. Web-App-Adressen und npm-/Vite-Schritte sind angepasst. Der frühere `app:setup`-Befehl und die Aktivierung über `web-setup.local.php` sind ersetzt; Details und bestehende Abschlusssperren siehe [Setup-Anleitung](setup.md).

Der aktuell registrierte Entwicklungsverbund besteht aus `php artisan serve`, `php artisan queue:listen --tries=1 --timeout=0` und `npm run dev`. Telescope-Bereinigung und Backups starten dadurch nicht automatisch.

## Installierte Anwendungserweiterungen

| Erweiterung / Version | Aufruf | Einstellungen und Status |
|---|---|---|
| **Fortify 1.41.0** | `/login`, `POST /logout`, `/user/confirm-password` | `config/fortify.php`, `FortifyServiceProvider`; Guard `web`, Ziel `/dashboard`, sechs Login-Anfragen pro Minute/IP. Native E-Mail-Verifikation aktiv; Registrierung, Reset, Profiländerungen, 2FA und Passkeys deaktiviert. Dashboard, Filament und Runner verlangen bestätigte Adressen. [Anleitung](fortify.md) |
| **Filament 5.10.0** | `/admin`, `/admin/login`; `php artisan filament:upgrade` | `AdminPanelProvider`, eigenes Standarddesign, Dashboard, strikte Autorisierung. Zugriff mit bestätigter E-Mail und `access-admin`; noch keine fachlichen Resources. [Anleitung](filament.md) |
| **Spatie Permission 8.3.0** | `php artisan permission:show`, `php artisan permission:cache-reset` | `config/permission.php`, `User::HasRoles`; Teams deaktiviert, Cache 24 Stunden. Middleware `role`, `permission`, `role_or_permission`. [Anleitung](permissions.md) |
| **Artisan Runner UI 1.0.0** | `/artisan-runner-ui` | Lokal aktiviert; auch in `.env.example` aktiviert. Login, bestätigte E-Mail und `run-artisan-runner-ui` erforderlich. Freigegeben: `about`, `migrate:status`, `permission:show`, `permission:cache-reset`. [Anleitung](artisan-runner-ui.md) |
| **Socialite 5.31.0** | Später im Controller `Socialite::driver('anbieter')->redirect()` / `->user()` | Paket installiert; noch kein Anbieter, Callback oder Social-Login aktiviert. Konfiguration später in `config/services.php`. [Anleitung](socialite.md) |
| **Spatie Media Library 11.23.9** | Später am Medienmodell `addMedia(...)->toMediaCollection(...)`; `php artisan media-library:regenerate` | `config/media-library.php`, `media`-Tabelle migriert. Fachliche Modelle und Upload-/Download-Endpunkte fehlen noch. [Anleitung](media-library.md) |
| **Eloquent-Sluggable 13.0.1** | Trait `Sluggable` und Modellmethode `sluggable()`; Erzeugung beim Speichern | `config/sluggable.php`; eindeutige Slugs, erster Suffix `2`, `onUpdate=false`. Fachliche Inhaltsmodelle fehlen noch. [Anleitung](sluggable.md) |
| **Tinker 3.0.2** | `php artisan tinker` | Interaktive PHP-/Eloquent-Konsole; benötigt keinen Web-Endpunkt |

Benutzer mit verdeckter Passworteingabe und optionalen Rechten anlegen:

```powershell
php artisan app:create-user deine-adresse@example.com --name="Dein Name"
php artisan app:create-user admin@example.com --name="Admin" --admin --runner
```

Keine Standardzugangsdaten angelegt. `--admin` und `--runner` erteilen getrennte Berechtigungen. Die normale Anmeldung vergibt keine Rechte automatisch. Zusätzliche Rollen und Policies müssen fachlich festgelegt werden.

## E-Mail-Verifikation und Session-API

Seit 2026-10-13 ist native E-Mail-Verifikation aktiviert. Browser-Einstieg `/email/verify`; JSON-Actions unter `GET /api/email-verification/status`, `POST /api/email-verification/notification` und `GET /api/email-verification/verify/{id}/{hash}`. Anmeldung über die bestehende Web-Session, CSRF für POST, gültige Signatur für Bestätigung. Neue CLI-/Setup-Konten erhalten Benachrichtigungen und bleiben bis zur Bestätigung unverifiziert. Mailer `log` stellt keine Mails zu. Vollständiger Ablauf und Beispiele: [email-verification.md](email-verification.md).

## Installierte Entwicklungswerkzeuge

| Werkzeug / Version | Aufruf | Einstellungen und Einschränkungen |
|---|---|---|
| **Debugbar 4.4.4** | Automatisch am unteren Rand geeigneter HTML-Seiten | `config/debugbar.php`; nur `local` + `APP_DEBUG=true` + `DEBUGBAR_ENABLED=true`; Speicherung `storage/debugbar` |
| **Telescope 5.25.0** | `/telescope`; `php artisan telescope:prune --hours=48` | Nur lokal und aktiviert; `config/telescope.php`. Lokal standardmäßig ohne Anmeldung. Sensible Formularfelder/Header werden maskiert. Tägliche Bereinigung durch Scheduler; Tests deaktivieren Telescope. |
| **Laravel-Brain 2.7.1** | `php artisan brain:scan --no-interaction`, `/_laravel-brain` | Nur lokal; Dateitreiber, 1024 MB, registrierte Routen; MCP deaktiviert. Graph in `storage/app/laravel-brain`. Lokal ohne Anmeldung, daher Server auf Loopback betreiben. KI-Regelerzeugung darf `AGENTS.md` nicht überschreiben. [Anleitung](laravel-brain.md) |
| **Pint 1.32.1** | `php vendor/bin/pint --test`; ohne `--test` formatieren | PHP-Codeformatierung; Laravel-Stil und vier Leerzeichen |
| **PHPUnit 12.5.38** | `php artisan test` oder `php vendor/phpunit/phpunit/phpunit` | Tests unter `tests/Feature` und `tests/Unit` |
| **PAO 1.1.5** | Automatisch bei unterstützten Test-/Formatter-Aufrufen | Ausgabeaufbereitung; bei Bedarf `$env:PAO_DISABLE='true'` für normale Werkzeugausgabe |
| **Pail 1.2.7** | `php artisan pail` | Installiert, benötigt aber `pcntl`; unter nativem Windows nicht lauffähig. Alternative: `Get-Content storage/logs/laravel.log -Tail 100 -Wait` |
| **Faker 1.24.1** | Factory, z. B. `User::factory()->create()` in Tests | Testdaten; Faker-Sprache `en_US` |
| **Mockery 1.6.15** | Testcode, z. B. `Mockery::mock(...)` | Test-Doubles; kein eigener Web-Endpunkt |
| **Collision 8.9.5** | Automatisch bei Artisan-/Testfehlern | Lesbare CLI-Fehlerausgabe |

Entwicklungsabhängigkeiten fehlen bei `composer install --no-dev`. Der eigene Telescope-Provider wird deshalb nur registriert, wenn das Paket lokal verfügbar ist.

Weitere wichtige **transitive** Pakete: Livewire **4.4.7** für Filament, Laravel Passkeys **0.2.1** als Fortify-Abhängigkeit (Funktion deaktiviert), Laravel Stress **1.0.3** als Brain-Abhängigkeit (keine Lasttests ausgeführt), Cocur Slugify **4.7.1** für Sluggable. Die übrigen Bibliotheken und Frontend-Pakete sind vollständig im [Abhängigkeitsverzeichnis](abhaengigkeiten.md) aufgeführt; sie werden über ihre übergeordneten Pakete verwendet.

## Backup mit Kopia

Kopia CLI **0.23.1** ist ein eigenständiges Windows-Programm. `config/backup.php`: Binary über `KOPIA_BINARY`, Timeout 3600 Sekunden, Staging unter `storage/app/backup-staging`.

```powershell
php artisan app:backup --prepare-only
php artisan app:backup
kopia repository status
kopia snapshot list
```

Der erste Befehl erstellt eine geprüfte SQLite-Kopie. Die eigentliche Sicherung erfordert ein verbundenes verschlüsseltes Repository. Endgültiges Ziel, Aufbewahrung und Windows-Zeitplanung sind noch offen. MariaDB-Backups benötigen zuvor einen ergänzten SQL-Dump. Kein Backup-Web-Endpunkt eingerichtet. [Vollständige Kopia-Anleitung](kopia.md).

## Tests und MariaDB-Vorbereitung

`phpunit.xml` verwendet Umgebung `testing`, SQLite `:memory:`, Array-Cache/-Sessions/-Mail, synchrone Queue und Bcrypt-Kostenfaktor 4. Die Anwendung nutzt lokal weiterhin SQLite. `phpunit.mariadb.xml` erzwingt MariaDB und `laravel_webapp_test`; Server, Testbenutzer und Zugangsdaten müssen zuerst bereitgestellt werden.

```powershell
php artisan test
php artisan test --configuration=phpunit.mariadb.xml
```

MariaDB-Tests können Tabellen der angegebenen Testdatenbank neu erstellen. Sie ausschließlich auf einer separaten Testdatenbank ausführen. Zuletzt geprüfter Stand nach E-Mail-Verifikation am 2026-10-13: 63 Tests, 795 Assertions; kein festgelegtes Coverage-Minimum. [MariaDB-Anleitung](mariadb.md).

## Pflege der Dokumentation

Bei Paketänderungen Lockfile-Versionen, Aufrufe und Aktivierungsstatus nachführen. Neue `.env`-Werte als Platzhalter in `.env.example` ergänzen. Vertrauliche Werte gehören weder in diese Übersicht noch in die Konfigurationsreferenz. Geplante und umgesetzte Arbeiten zusätzlich im Projektlog festhalten.
