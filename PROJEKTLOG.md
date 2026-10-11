# Projektdokumentation und Aufgaben

Stand: 07.10.2026. Diese Datei ist die zentrale Übersicht über durchgeführte Arbeiten, Entscheidungen, Prüfungen und offene Aufgaben.

## Pflege dieser Datei

Bei künftigen Arbeiten diese Datei im selben Arbeitsschritt aktualisieren: Datum, Anlass, Änderungen, betroffene Dateien, Prüfungen und verbleibende Aufgaben festhalten. Erledigte Aufgaben markieren und neue wichtige Aufgaben ergänzen. Auch fehlgeschlagene Prüfungen und ihre Lösung dokumentieren, wenn sie für weitere Arbeiten relevant sind. Passwörter, Schlüssel und andere Zugangsdaten niemals aufnehmen. Die bisherigen Arbeiten sind nachfolgend aus dem Sitzungsverlauf zusammengefasst.

## Durchgeführte Arbeiten – 07.10.2026

### Repository-Leitfaden

- Im zunächst leeren Projektverzeichnis `AGENTS.md` mit Repository Guidelines erstellt (372 Wörter).
- Auf ausdrücklichen Wunsch anschließend unverändert erhalten. Die darin enthaltene Beschreibung als leeres Projekt entspricht deshalb dem ursprünglichen Zustand.

### Laravel-Basisinstallation

- Laravel Framework 13.35.0 mit Composer installiert; Abhängigkeiten in `composer.lock` festgehalten.
- Serverseitiges Frontend mit Blade, Vite und Tailwind eingerichtet. Standard-Startseite unter `/` erhalten.
- `.env` und Anwendungsschlüssel erstellt; lokale SQLite-Datenbank angelegt und Standardmigrationen für Benutzer, Sessions, Cache und Queues ausgeführt.
- PHP 8.5.6, Composer 2.9.8 und Node 24.18.0 auf der Entwicklungsmaschine geprüft.
- Frontend-Abhängigkeiten installiert und `package-lock.json` erstellt. Eine gemeldete Sicherheitslücke in `shell-quote` durch den Override `^1.12.0` in `package.json` behoben. Anschließender npm-Audit ohne Befunde; Build und Standardtests erfolgreich.

### Spatie Laravel Permission

- Version 8.3.0 installiert, Versionsbereich `^8.3` festgelegt.
- `HasRoles` in `app/Models/User.php` ergänzt.
- `config/permission.php` und Migration veröffentlicht; Berechtigungstabellen lokal angelegt und Berechtigungscache zurückgesetzt.
- Middleware-Aliase `role`, `permission` und `role_or_permission` in `bootstrap/app.php` registriert.
- Tests für Rollenvererbung, direkte Berechtigungen, Berechtigungsentzug und gesperrte Zugriffe ergänzt. Keine allgemeinen Standardrollen angelegt.
- Anleitung: [docs/permissions.md](docs/permissions.md).

### Vorbereitung auf MariaDB

- Vorhandene Laravel-Verbindung `mariadb` geprüft: `utf8mb4`, `utf8mb4_unicode_ci`, strikter SQL-Modus. `pdo_mysql` ist lokal vorhanden.
- `.env.example` um kommentierte MariaDB-Werte ergänzt; aktive Anwendung weiterhin auf SQLite.
- `phpunit.mariadb.xml` für die separate Datenbank `laravel_webapp_test` erstellt. Verbindung, Datenbankname und leerer `DB_URL` werden darin erzwungen.
- `.env.testing` in `.gitignore` aufgenommen. MariaDB-Testkonfiguration durch Auflisten der Tests geprüft; keine echte MariaDB-Verbindung hergestellt.
- Anleitung: [docs/mariadb.md](docs/mariadb.md).

### Artisan Runner UI

- `codesquirrel/artisan-runner-ui` 1.0.0 installiert, Versionsbereich `^1.0` festgelegt und `config/artisan-runner-ui.php` veröffentlicht.
- Lokal in `.env` aktiviert; `.env.example` lässt die UI standardmäßig deaktiviert.
- Verfügbarkeit auf `local` beschränkt. Zugriff erfordert Anmeldung und `run-artisan-runner-ui` im Guard `web`.
- Nur `about`, `migrate:status`, `permission:show` und `permission:cache-reset` freigegeben; zusätzliche Paket-Sperrliste erhalten.
- GET `/artisan-runner-ui` und POST `/artisan-runner-ui/run` geprüft. Das Paket protokolliert Ausführungen im Laravel-Log.
- Tests für Umgebung, Gäste, fehlende Berechtigungen, erlaubte Ausführung und Ablehnung von `migrate:fresh` ergänzt.
- Anleitung: [docs/artisan-runner-ui.md](docs/artisan-runner-ui.md).

### Anmeldung und Benutzeranlage

- `SessionController` und Blade-Seiten für `/login` und `/dashboard` ergänzt. Logout erfolgt per POST `/logout`.
- Formularvalidierung, CSRF-Formulare, Begrenzung auf sechs Login-Anfragen je Minute und IP, Session-Erneuerung nach Login sowie Session-Invalidierung und CSRF-Token-Erneuerung nach Logout eingerichtet.
- Geschützte Zugriffe führen Gäste zur Anmeldung; anschließend Rückkehr zur ursprünglich angefragten Seite. Die Übersicht enthält bei Berechtigung und aktivierter UI einen Runner-Link.
- `app:create-user` mit verdeckter Passwortabfrage, Bestätigung, mindestens zwölf Zeichen und Prüfung auf doppelte E-Mail-Adressen implementiert. `--runner` vergibt die Runner-Berechtigung. Benutzeranlage und Vergabe erfolgen in einer Datenbanktransaktion.
- Keine echten Benutzer oder voreingestellten Zugangsdaten angelegt. Öffentliche Registrierung und Passwort-Wiederherstellung sind bisher nicht implementiert.
- Authentifizierungs- und Konsolentests sowie Runner-Test mit tatsächlicher Anmeldung ergänzt.

### Verifikation und technische Besonderheiten

- Letzte vollständige Prüfung: **15 Tests, 56 Assertions erfolgreich**; Pint-Prüfung und Frontend-Build erfolgreich. Composer-Konfiguration und Lockfile validiert; Composer-Audit ohne Befunde zum Installationszeitpunkt.
- Sandbox-Einschränkungen verhinderten teilweise npm-Downloads, Vite-Unterprozesse und das Ersetzen kompilierter Blade-Dateien unter Windows. Erneute Prüfungen mit passenden Prozessrechten bestanden. Ein blockierter Composer-Lauf wurde beendet und anhand des Lockfiles erfolgreich fortgesetzt.
- Für die direkte PHPUnit-Prüfung wurde temporär `PAO_DISABLE=true` gesetzt; keine dauerhafte Änderung der Anwendungskonfiguration.
- Vite meldet einen optionalen Hinweis zu `fontaine` für optimierte Schrift-Fallbacks; der Build funktioniert ohne diese Zusatzabhängigkeit.

### Zentrale Dokumentation

- Diese Datei erstellt und bisherige Arbeiten sowie offene Aufgaben zusammengeführt. Für diese reine Dokumentationsänderung keine zusätzlichen Anwendungstests ausgeführt.

### Laravel Debugbar – 07.10.2026

- Das aktuelle Paket `fruitcake/laravel-debugbar` 4.4.4 mit Versionsbereich `^4.4` als Entwicklungsabhängigkeit installiert (Nachfolger des früheren Paketnamens `barryvdh/laravel-debugbar`). Laravel registriert den Service Provider automatisch.
- `config/debugbar.php` ergänzt: Aktivierung nur bei `APP_ENV=local`, `APP_DEBUG=true` und `DEBUGBAR_ENABLED=true` (lokaler Standard). Weitere Einstellungen stammen aus den Paketdefaults.
- `DEBUGBAR_ENABLED=true` in `.env.example` dokumentiert; für die bestehende lokale `.env` greift der Standard. Abschalten mit `DEBUGBAR_ENABLED=false` und `php artisan config:clear`.
- Lokale Anfrageprofile in `storage/debugbar` von Git ausgeschlossen. Keine Migration oder zusätzlicher Frontend-Build erforderlich.
- Für Deployment mit `composer install --no-dev` installieren; Debugbar ist ausschließlich für lokale Entwicklung vorgesehen.
- Prüfung: 15 Tests und 56 Assertions erfolgreich; Pint-Prüfung bestanden. Mit kurzzeitigem lokalem PHP-Server verifiziert: Debugbar wird in `/login` unter `local` eingebunden, bei `DEBUGBAR_ENABLED=false` nicht eingebunden und unter `production` auch bei explizitem Aktivierungswunsch nicht eingebunden. Prüfserver anschließend beendet.
- Composer blieb bei der Autoload-Generierung ohne Fortschrittsausgabe. Den identifizierten Installationsprozess beendet und anhand des Lockfiles mit temporär deaktiviertem Xdebug erfolgreich fortgesetzt. Versionsbereich anschließend festgelegt und Lockfile synchronisiert.

### Laravel Telescope – 07.10.2026

- `laravel/telescope` 5.25.0 mit Versionsbereich `^5.25` als Entwicklungsabhängigkeit installiert; Composer-Audit ohne Befunde und Manifest/Lockfile validiert.
- `telescope:install` ausgeführt: `config/telescope.php`, `app/Providers/TelescopeServiceProvider.php` und Migration für Telescope-Einträge veröffentlicht. Migration lokal auf SQLite ausgeführt.
- Auto-Discovery für Telescope in `composer.json` deaktiviert; Registrierung beider Telescope-Provider erfolgt in `AppServiceProvider` ausschließlich bei `APP_ENV=local`, aktiviertem Telescope und vorhandenem Paket. Keine feste Provider-Registrierung in `bootstrap/providers.php`; damit bleibt die Anwendung bei `composer install --no-dev` lauffähig.
- `TELESCOPE_ENABLED=true` in `.env.example` ergänzt. Die bestehende lokale Installation verwendet den aktivierten Paketstandard. Abschalten mit `TELESCOPE_ENABLED=false` und `php artisan config:clear`; Dashboard und Watcher werden dann nicht registriert.
- Dashboard: `/telescope`. In der lokalen Umgebung ist es gemäß Laravel-Standard ohne Anmeldung verfügbar; außerhalb von `local` fehlen Dashboard und API-Routen vollständig. Nur auf dem lokalen Entwicklungsserver verwenden.
- Passwörter, Passwortbestätigung, aktuelles Passwort und CSRF-Parameter sowie Cookie-, Authorization- und CSRF-Header werden auch in lokalen Request-Einträgen ausgeblendet.
- Tägliche Bereinigung von Einträgen älter als 48 Stunden in `routes/console.php` geplant. Sie benötigt einen laufenden Scheduler; alternativ `php artisan telescope:prune --hours=48` manuell ausführen. Aufzeichnungen liegen in der aktiven Datenbank; bei späterem MariaDB-Wechsel legt die veröffentlichte Migration die Tabellen dort an.
- Prüfung: 15 Tests, 56 Assertions erfolgreich; abschließende Pint-Prüfung bestanden. HTTP-Prüfung mit anschließend beendetem lokalem PHP-Server: Dashboard unter `local` liefert 200; bei `TELESCOPE_ENABLED=false` und unter `production` auch mit aktiviertem Schalter jeweils 404. Eine markierte Login-Anfrage wurde tatsächlich in `telescope_entries` gespeichert; per direkter SQLite-Abfrage ohne Ausgabe von Request-Inhalten bestätigt.
- Kein zusätzlicher npm-Build nötig; Telescope liefert seine Dashboard-Assets selbst. Die bestehende MariaDB-Verbindung wurde weiterhin nicht getestet.

### Bereinigung und Backup-Kompatibilität – 07.10.2026

- Die bereits vom Benutzer zurückgenommenen Queue-Monitoring-Abhängigkeiten anhand von `composer.json` und `composer.lock` überprüft. Verbliebenen Service Provider, Konfiguration, Tests und Anleitung entfernt; Composer-Installation mit dem bereinigten Lockfile synchronisiert und übrig gebliebene Pakete entfernt.
- Veraltete Dokumentationsverweise und Testcache entfernt; Konfigurations- und Routencaches geleert. Composer meldete Warnungen in den Pre-Uninstall-Ereignissen, führte Paketentfernung, Autoload-Generierung und Package Discovery dennoch erfolgreich aus.
- Vor einer neuen Installation die offiziellen Anforderungen von Spatie Laravel Backup v10 geprüft: Windows-Server werden ausdrücklich nicht unterstützt. Paket deshalb noch nicht installiert; Entscheidung zwischen einer Windows-tauglichen Lösung und Integration für einen späteren Betrieb auf einem unterstützten System angefragt.
- Neue verbindliche Arbeitsregel: Betriebssystem-Kompatibilität vor jeder Paketinstallation prüfen. Einschränkungen gegenüber der vorhandenen Windows-Laufzeit vor Installation mitteilen und die weitere Vorgehensweise klären.
- Referenz: https://spatie.be/docs/laravel-backup/v10/requirements
- Abschlussprüfung der Bereinigung: Keine verbliebenen Verweise auf das entfernte Produkt in Anwendung, Konfiguration, Tests, Projektanleitungen, Bootstrap-Caches oder Composer-Dateien. Paketverzeichnisse entfernt; Composer-Konfiguration gültig. Alle 15 Tests mit 56 Assertions bestanden.

### Kopia unter Windows – 07.10.2026

- Kopia als Windows-kompatible Alternative ausgewählt; offizielle Windows-Unterstützung geprüft. CLI 0.23.1 mit `winget install --id Kopia.KopiaCLI --exact --source winget --scope user` installiert; Installer-Prüfsumme von winget validiert und Version tatsächlich ausgeführt.
- `config/backup.php` und `app:backup` eingerichtet. Die lokale `.env` enthält den tatsächlichen Kopia-Programmpfad, da das laufende Terminal den aktualisierten PATH noch nicht übernommen hatte. `.env.example` enthält den portablen Standard `KOPIA_BINARY=kopia`.
- SQLite-Vorbereitung mit `VACUUM INTO` und `PRAGMA integrity_check` implementiert. `--prepare-only` bereitet ausschließlich die konsistente Datenbankkopie vor; normaler Lauf prüft die Kopia-Verbindung und erstellt danach einen Projektsnapshot. Fehler werden als fehlgeschlagener Befehl gemeldet; Cache-Sperre verhindert parallele Aufrufe. Kopia verwendet `--fail-fast`.
- `.kopiaignore` ergänzt: Abhängigkeiten, Git-Verzeichnis, Caches, Logs, Debugbar-Profile und rohe SQLite-Dateien unter `database/` ausgeschlossen. Quellcode, Lockfiles, lokale Umgebungsdateien, Uploads und die vorbereitete Datenbankkopie enthalten. Lokales Staging-Verzeichnis von Git ausgeschlossen.
- Keine Repository-Zugangsdaten in Git oder Dokumentation gespeichert. Der endgültige Speicherort wurde angefragt; produktives Repository, Aufbewahrung und Zeitplan deshalb noch nicht eingerichtet. Künstliches Testrepository mit zufälligem temporärem Passwort ausschließlich zur Verifikation erstellt und danach entfernt.
- Prüfung: Gesamte Laravel-Suite mit 18 Tests und 64 Assertions bestanden; Pint-Prüfung bestanden. Tests decken konsistente SQLite-Kopie, fehlendes Repository und fehlgeschlagenen Snapshot ab. SQLite-Kopie zusätzlich auf der aktuellen lokalen Installation erstellt und geprüft. Verschlüsselten Snapshot und Restore mit künstlichen Testdaten ausgeführt; Aufnahme von `.env`, Upload und Staging-Kopie sowie Ausschluss von Abhängigkeiten und Rohdatenbank verifiziert.
- Der Backup-Befehl unterstützt derzeit SQLite. Bei MariaDB wird bewusst abgebrochen, bis ein konsistenter SQL-Dump integriert ist. Dateien und Datenbank sind kein gemeinsamer atomarer Snapshot; bei strikten Anforderungen Schreibzugriffe während der Sicherung pausieren.
- Anleitung für Einrichtung, Aufbewahrung, Windows-Aufgabenplanung und Restore: [docs/kopia.md](docs/kopia.md). Keine Änderung der Composer-Abhängigkeiten erforderlich.

### Laravel Socialite – 07.10.2026

- Windows-Kompatibilität vor Installation anhand der Paketanforderungen geprüft: keine Linux-spezifischen Erweiterungen erforderlich; Laravel 13 wird unterstützt.
- `laravel/socialite` 5.31.0 mit Versionsbereich `^5.31` als reguläre Composer-Abhängigkeit hinzugefügt und Lockfile synchronisiert. Registrierung erfolgt über Laravel Package Discovery. Keine Migrationen oder Frontend-Builds erforderlich.
- Anleitung für spätere Anbieter-Konfiguration in `config/services.php`, lokale OAuth-Zugangsdaten, Callback und sichere Kontoverknüpfung unter [docs/socialite.md](docs/socialite.md) ergänzt. Noch keine Anbieterwahl getroffen; keine Social-Login-Routen oder automatischen Benutzerverknüpfungen eingerichtet.
- Composer blieb bei der Autoload-Generierung ohne Fortschrittsausgabe; identifizierte Installationsprozesse beendet und anhand des Lockfiles erfolgreich fortgesetzt. Composer-Konfiguration validiert, Paket-Audit ohne Befunde. Alle 18 Tests mit 64 Assertions bestanden. Auflösung des Socialite-Factory-Contracts als SocialiteManager im tatsächlichen Laravel-Container geprüft; kein externer OAuth-Aufruf durchgeführt.

### Spatie Media Library – 07.10.2026

- Windows- und Laravel-13-Kompatibilität anhand der Paketanforderungen geprüft. PHP verfügt lokal über EXIF, Fileinfo, GD und Imagick; keine Linux-spezifischen Erweiterungen erforderlich.
- `spatie/laravel-medialibrary` 11.23.9 mit Versionsbereich `^11.23` als reguläre Composer-Abhängigkeit installiert und Lockfile synchronisiert. Media Library Pro ist nicht Bestandteil der Installation.
- Konfiguration `config/media-library.php` und Migration veröffentlicht; Tabelle `media` auf der lokalen SQLite-Datenbank angelegt. Privaten Standard-Speicher eingerichtet (`MEDIA_DISK=local`, `storage/app/private`) und in `.env.example` dokumentiert. Bildtreiber ist GD; Paketstandard für asynchrone Konvertierungen verwendet die bestehende Queue. Fachliche Modelle, Upload-Routen und Oberfläche werden erst nach Festlegung der Anforderungen ergänzt.
- Integrationstest mit ausschließlich testinternem Modell für Dateizuordnung, Abruf, Speicherung und Löschung beim Entfernen des Modells erfolgreich ausgeführt; produktives User-Modell dafür nicht verändert.
- Anleitung unter [docs/media-library.md](docs/media-library.md) ergänzt. Optionale Werkzeuge für PDF-/SVG-/Video-Vorschaubilder und Bildoptimierung nicht installiert. Kopia berücksichtigt den lokalen Medienspeicher bereits über `storage/app`.
- Composer blieb wiederholt bei optimierter Autoload-Generierung stehen. Identifizierte Installationsprozesse beendet, Paketmetadaten anhand des Lockfiles synchronisiert und für diese lokale Installation normale PSR-Autoload-Dateien mittels temporärer Composer-Konfiguration erzeugt. Temporäre Datei entfernt; Einstellung zur Autoload-Optimierung in der eigentlichen `composer.json` nicht geändert. Package Discovery und tatsächliche Paketnutzung erfolgreich geprüft.
- Abschlussprüfung: 19 Tests mit 71 Assertions bestanden, Pint-Prüfung bestanden, Composer-Konfiguration gültig und Paket-Audit ohne Befunde. Migration erfolgreich; keine zusätzlichen Frontend-Builds notwendig.

## Filament – 2026-10-07

- Filament 5.10.0 mit Livewire 4.4.7 installiert. Laravel-/PHP-Anforderungen anhand der offiziellen Installation und Composer geprüft; erfolgreich auf Windows installiert, keine Linux-Pflicht.
- Admin-Panel unter `/admin` mit Login `/admin/login`, Dashboard und dem vorhandenen `web`-Guard eingerichtet. Provider registriert, Assets veröffentlicht und `filament:upgrade` im Composer-Autoload-Script ergänzt. Veröffentlichte Assets sind generiert und werden nicht versioniert.
- `User` implementiert `FilamentUser`. Zugriff erfordert ausdrücklich die Spatie-Berechtigung `access-admin`, auch lokal. Strikte Autorisierung für spätere Resources aktiviert; eigene Policies bleiben erforderlich.
- `app:create-user --admin` ergänzt. Passwort wird verdeckt eingegeben; kein Standardadministrator angelegt. Berechtigte Benutzer sehen den Panel-Link auf der bestehenden Übersicht.
- Einrichtung, Zugriffsvergabe und Weiterentwicklung in [docs/filament.md](docs/filament.md) dokumentiert. Noch keine fachlichen Resources oder zusätzlichen Integrationsplugins angelegt.
- Composer zunächst ohne Autoload/Scripts installiert und lokale Autoload-Dateien über temporäre Konfiguration erstellt. Temporäre Datei entfernt; eigentliche Optimierungseinstellung unverändert. Panel-Installer zur vollständigen Asset-Veröffentlichung erneut ausgeführt.
- Abschlussprüfung: 25 Tests mit 99 Assertions bestanden, Pint bestanden, Composer-Konfiguration gültig, Paket-Audit ohne Befunde. Admin-, Login- und Logout-Routen geprüft; Assets erfolgreich veröffentlicht.

## Eloquent-Sluggable – 2026-10-08

- `cviebrock/eloquent-sluggable` 13.0.1 und `cocur/slugify` 4.7.1 installiert; Composer-Versionsbereich `^13.0`. Laravel-13-Kompatibilität anhand der Paketdokumentation und Composer geprüft; Installation unter Windows erfolgreich.
- Automatische Provider-Registrierung geprüft und `config/sluggable.php` veröffentlicht. Eindeutige Slugs und stabile URLs mit `unique=true` und `onUpdate=false` beibehalten.
- Anleitung mit Modell-Trait, Quellfeld, eindeutiger Datenbankspalte und optionalen Slug-Routen unter [docs/sluggable.md](docs/sluggable.md) ergänzt. Bisher existiert kein fachliches Inhaltsmodell; daher keine Produktivtabellen oder User-Slugs ergänzt.
- Integrationstest mit isolierter Testtabelle prüft automatische Erzeugung, Suffix bei doppelten Titeln und Erhalt des Slugs nach Titeländerung.
- Composer ohne automatische Scripts installiert; Autoload über temporäre Konfiguration erzeugt, anschließend Package Discovery und Konfigurationsveröffentlichung ausgeführt. Temporäre Datei entfernt; reguläre Autoload-Optimierung unverändert.
- Vollständige Tests deckten eine bestehende Kopia-Testsimulation auf, deren Stringmuster nicht zum Prozessaufruf passte. Simulation vergleicht jetzt das tatsächliche Befehlsarray; Backup-Produktivcode unverändert.
- Abschlussprüfung: 27 Tests mit 102 Assertions bestanden, Pint bestanden, Composer-Konfiguration gültig und Paket-Audit ohne Befunde.

## Laravel Fortify – 2026-10-08

- Fortify 1.41.0 mit kompatiblen Abhängigkeiten installiert und unter Windows geprüft. Composer-Metadaten und Lockfile aktualisiert; temporäre Autoload-Konfiguration nach Verwendung entfernt.
- `config/fortify.php` und registrierten `FortifyServiceProvider` ergänzt. Fortify übernimmt Login/Logout über den vorhandenen `web`-Guard, bestehendes Blade-Login, Weiterleitung nach `/dashboard` und Session-Invalidierung. Login-Limit von sechs Anfragen pro Minute und IP beibehalten.
- Parallele Auth-Routen und bisherigen `SessionController` entfernt. Passwortbestätigung mit eigener Blade-Seite unter `/user/confirm-password` eingerichtet.
- Optionale Kontofunktionen zunächst deaktiviert, da deren Anforderungen noch offen sind. Keine zusätzlichen User-Spalten oder Migrationen notwendig. Filament-Anmeldung und Spatie-Zugriffsschutz mit bestehender Testsuite geprüft.
- Anleitung und Erweiterungsvoraussetzungen in [docs/fortify.md](docs/fortify.md) dokumentiert. Tests für Passwortbestätigung und deaktivierte optionale Funktionen ergänzt.
- Abschlussprüfung: 29 Tests mit 114 Assertions bestanden, Pint bestanden, Composer-Konfiguration gültig und Audit ohne Befunde; Vite-Produktionsbuild erfolgreich. Build meldet einen optionalen Hinweis auf `fontaine` für optimierte Schrift-Fallbacks; keine zusätzliche Abhängigkeit installiert.

## Laravel-Brain – 2026-10-08

- `laramint/laravel-brain` 2.7.1 als Entwicklungsabhängigkeit installiert; `laramint/laravel-stress` 1.0.3 wird vom Paket mitgeliefert. Laravel-13-Kompatibilität geprüft und Scan unter Windows erfolgreich ausgeführt. Keine Lasttests gestartet.
- Dateitreiber, Speicherlimit und Erkennung registrierter Routen in `config/laravel-brain.php` vorbereitet. Optionalen MCP-Server deaktiviert. Keine neue Datenbank oder Migration erforderlich.
- Automatische Paketregistrierung geprüft. Graphdateien in `storage/app/laravel-brain/` erzeugt und von Git ausgeschlossen. Vollständiger Scan: 77 Routen, 223 Knoten, 262 Kanten. Anleitung unter [docs/laravel-brain.md](docs/laravel-brain.md).
- Viewer `/_laravel-brain` mit HTTP-Prüfung bestätigt: lokal 200, Produktion 404. Für den ersten Produktions-Probelauf wurde die Testumgebung nicht korrekt übergeben; nach expliziter Übergabe erneut geprüft und Sperre bestätigt. Temporäre Prüfdatei anschließend entfernt.
- Keine KI-Regeldateien generiert; `AGENTS.md` unverändert. Composer-Autoload mit temporärer Konfiguration aufgebaut und temporäre Datei entfernt.
- Scanner meldet 31 heuristische Security-Hinweise; keine bestätigten Schwachstellen daraus abgeleitet. Paket-Audit und Anwendungstests gesondert geprüft.
- Abschlussprüfung: 29 Tests mit 114 Assertions bestanden, Pint bestanden, Composer-Konfiguration gültig und Paket-Audit ohne Befunde. Kein zusätzlicher Frontend-Build erforderlich; Viewer-Assets werden vom Paket bereitgestellt.

## Wichtige offene Aufgaben

### Gesamtdokumentation – 2026-10-08

- [docs/installationsuebersicht.md](docs/installationsuebersicht.md) als zentralen Einstieg erstellt: Laravel-Grundeinstellungen, aktive und vorbereitete Funktionen, Erweiterungen mit Versionen, Browser-Adressen, Artisan-/PowerShell-Aufrufen, Zugriffsrechten und Betriebsbedingungen.
- [docs/konfigurationsreferenz.md](docs/konfigurationsreferenz.md) aus der effektiv geladenen lokalen Konfiguration erzeugt: sämtliche 31 Konfigurationsbereiche einschließlich Framework- und Paketdefaults. Zugangsdaten und Schlüssel maskiert; normale Einstellungen wie Passwort-Broker und Bestätigungs-Timeout bleiben lesbar.
- [docs/abhaengigkeiten.md](docs/abhaengigkeiten.md) ergänzt: alle 184 PHP-Pakete mit direkter/transitiver Herkunft und Entwicklungs-/Anwendungsbereich sowie JavaScript-Lockfile-Einträge einschließlich optionaler Plattformpakete.
- [docs/artisan-befehle.md](docs/artisan-befehle.md) aus der tatsächlich verfügbaren lokalen Befehlsliste erstellt; dokumentiert Aufruf und Beschreibung, ohne Befehle auszuführen. README mit zentralen Dokumentationslinks ergänzt.
- Native Windows-Grenze des bereits installierten Pail dokumentiert (`pcntl` erforderlich), einschließlich PowerShell-Alternative. Keine neue Software installiert und keine Anwendungseinstellungen verändert.
- Dokumentation gegen Konfiguration, Provider, Umgebungs-Vorlage, freigegebene lokale Werte, Lockfiles und CLI-Verzeichnisse geprüft. Lokale Links gültig und App-Schlüssel nicht enthalten. Temporäres Generierungsscript entfernt. Für reine Dokumentationsänderungen keine erneute Anwendungstestsuite erforderlich.
- Referenzen sind eine Momentaufnahme; nach Konfigurations- oder Paketänderungen nachführen. Bereits offene fachliche und betriebliche Aufgaben bleiben in den folgenden Punkten erhalten.

- [ ] Laravel-Brain-Graph im Browser erkunden und heuristische Security-Hinweise im Kontext der lokalen Entwicklungswerkzeuge bewerten; nach Änderungen erneut `php artisan brain:scan --no-interaction` ausführen.

- [ ] Fortify-Funktionen für Registrierung, Reset, Verifikation, Profil, Zwei-Faktor-Anmeldung und Passkeys nach Bedarf spezifizieren; vor Aktivierung erforderliche Views, Actions, Mailkonfiguration und Migrationen implementieren.

- [ ] Inhaltsmodelle für Slugs festlegen, Quellfelder und eindeutige Slug-Spalten ergänzen; bestehende Datensätze gegebenenfalls kontrolliert nachbefüllen. Slug-Routen, reservierte Namen und Verhalten bei paralleler Erstellung definieren.

- [ ] Ersten Administrator interaktiv mit `php artisan app:create-user deine-adresse@example.com --name="Dein Name" --admin` anlegen und Panel im Browser prüfen.
- [ ] Fachliche Filament-Resources und deren Policies festlegen; Integrationen mit Media Library, Socialite oder Rollenverwaltung bei Bedarf ergänzen.

- [ ] Fachliche Medienmodelle, erlaubte Upload-Typen/-Größen, Berechtigungen und Collections festlegen; anschließend Uploads und geschützte Downloads implementieren. Benötigte Vorschaubild-Konvertierungen und Zusatzwerkzeuge gesondert festlegen.

- [ ] OAuth-Anbieter und Regeln für Social Login festlegen, OAuth-Anwendung beim Anbieter erstellen und anschließend Callback, Kontoverknüpfung und Integrationstests einrichten.

- [x] Windows-taugliche Backup-Lösung auswählen: Kopia installiert und mit Laravel-Befehl angebunden.
- [ ] Endgültigen Kopia-Speicherort festlegen und verschlüsseltes Repository mit lokal gewähltem Passwort verbinden; anschließend erste tatsächliche Sicherung und Wiederherstellung in separates Verzeichnis prüfen.
- [ ] Kopia-Aufbewahrung und automatische Ausführung per Windows-Aufgabenplanung festlegen.
- [ ] Beim MariaDB-Wechsel konsistenten SQL-Dump in `app:backup` integrieren und Wiederherstellung testen.

- [ ] Bei laufender lokaler Entwicklung den Scheduler aktivieren (`php artisan schedule:work`), damit die tägliche Telescope-Bereinigung ausgeführt wird; alternativ manuell `php artisan telescope:prune --hours=48` ausführen.

- [ ] Ersten tatsächlichen Benutzer mit eigenen Zugangsdaten interaktiv anlegen (Benutzeraktion; Passwort nicht dokumentieren).
- [ ] Anwendung im Browser prüfen: Login, Übersicht, Runner-Aufruf, Ausführung eines freigegebenen Befehls und Logout. Automatisierte HTTP-Tests bestehen bereits.
- [ ] Fachliche Anforderungen, Seitenstruktur und Gestaltung der Web-Anwendung festlegen.
- [ ] Konkrete Rollen und Berechtigungen definieren; bei Bedarf wiederholbar über Seeder einrichten.
- [ ] MariaDB-Server, Anwendungsdatenbank und separate Testdatenbank bereitstellen; getrennte Benutzer und lokale Zugangsdaten konfigurieren.
- [ ] Auf MariaDB umstellen, Migrationen ausführen und vollständige Tests gegen MariaDB durchführen. Falls SQLite-Daten erhalten werden sollen, deren Übertragung gesondert planen.
- [ ] Vor einem Deployment Hosting, HTTPS, Secrets, Backups, `APP_DEBUG=false`, Session-Einstellungen und den Betrieb der Queues festlegen. Runner UI bleibt außerhalb von `local` gesperrt.
- [ ] Bei Bedarf Anforderungen für Registrierung, Passwort-Wiederherstellung und E-Mail-Verifikation klären.

## Häufige Befehle

### Codex-Sitzung wieder aufnehmen – 2026-10-07

- `Resume-Codex.ps1` im Projektstamm ergänzt. Das Skript verwendet die feste ID dieser Hauptsitzung und startet `codex resume` im Projektverzeichnis. Andere, später gestartete Sitzungen beeinflussen die Auswahl nicht.
- Aufruf: `.\Resume-Codex.ps1`. Voraussetzung: Codex CLI im PATH, Anmeldung und gespeicherte Sitzung im selben lokalen Codex-Profil. Die bisherige Sitzung vor der Wiederaufnahme schließen.
- Optional `-SessionId <UUID>` für eine andere Sitzung; `-WhatIf` zeigt den geplanten Aufruf ohne Start. Das vorherige Arbeitsverzeichnis wird nach Beendigung wiederhergestellt. Keine Änderungen an Berechtigungen oder Ausführungsrichtlinien.
- Wiederaufnahmebefehl anhand der installierten CLI-Hilfe und [offiziellen OpenAI-Dokumentation](https://learn.chatgpt.com/docs/codex/cli) geprüft. PowerShell-Syntax und Probelauf mit `-WhatIf` geprüft; keine zweite aktive Sitzung gestartet.

```powershell
php artisan serve
npm run dev
npm run build
php artisan test
php vendor/bin/pint --test
php artisan app:create-user deine-adresse@example.com --name="Dein Name" --runner
```

Nach Bereitstellung der separaten MariaDB-Testdatenbank:

```powershell
php artisan config:clear
php artisan test --configuration=phpunit.mariadb.xml
```

MariaDB-Tests können Tabellen in der Testdatenbank neu anlegen. Dafür ausschließlich den Testbenutzer und die Testdatenbank verwenden.

## Interaktive Einrichtung (2026-10-11)

- Eigenständiger Einstieg `php setup.php`, alternativ `composer setup`, vor Installation der Composer-Abhängigkeiten lauffähig.
- Bestätigung je Schritt; Entwicklung/Produktion, sichere `.env`-Vorlage, Datenbankauswahl SQLite/MariaDB/MySQL und optionale Erstellung, CORS-Abfrage, Migrationen und Benutzer mit beiden Oberflächenrechten.
- Optionale npm-Installation, Asset-Build und Storage-Link; URLs und Startbefehle als Ausgabe bzw. lokale `SETUP.md`. Kopia bleibt separat.
- Vorhandene Schlüssel und Daten bleiben erhalten; Tests prüfen dotenv-Sonderzeichen und die sofortige Übernahme geänderter Datenbank-/CORS-Einstellungen.

## Browser-Einrichtung (2026-10-11)

- Eigenständiger Einstieg `public/setup.php` mit grafischem Dialog, vor Laravel-/Composer-Installation erreichbar; Aktivierung über separate, nicht versionierte `web-setup.local.php` außerhalb von `public/`.
- Zugangscode, HTTPS, kurzlebige Sitzung, CSRF-Schutz gegen Fremdanfragen und doppelte Bestätigungen, feste Aktionen und Sperrdatei nach Abschluss.
- Konfiguration, Migrationen und Benutzer mit beiden Berechtigungen direkt in PHP. Composer/npm nur bei explizit freigegebener Prozessausführung; andernfalls Anleitung zum Hochladen lokal vorbereiteter Abhängigkeiten und Assets.
- Abschlussübersicht mit registrierten URLs, Betriebsbefehlen und übersprungenen Schritten, optional als `SETUP.md`; Kopia und dauerhafte Dienste bleiben separat.
- Isolierte Tests für Migrationen, Benutzerrechte, Schlüssel-Erhaltung und echten HTTP-Dialog einschließlich Anmeldung, CSRF, fehlendem `vendor/` und Abschlusssperre.

## Setup an LaravelApi angeglichen (2026-10-11)

- Setup-Dateien, Browser-Template, Übersetzungen und Tests des benachbarten Projekts `C:\Entwicklung\Projekte\Boilerplates\LaravelApi` verglichen; dieses Referenzprojekt ausschließlich gelesen.
- Dessen Setup-Struktur übernommen: eigenständige CLI unter `scripts/Setup.php`, Windows-tauglicher Composer-Callback mit erhaltener STDIN, `SetupRuntime`/`SetupProcess`, Actions unter `app/Actions/Setup`, reines PHP-Template und Browser-Fortschrittsstrom. `php setup.php` bleibt zusätzlicher Einstieg; der bisherige `app:setup`-Befehl und seine doppelte Implementierung wurden ersetzt.
- Schritte wie im Referenzprojekt: bestätigte Composer-Installation ohne Hooks, Erhalt vorhandener `.env`/Schlüssel, Datenbankauswahl/Verbindungstest, bestätigte Migrationen, Package Discovery/Filament/Permission-Cache, optionale Admin-Rechte, Runner, Storage-Link und Brain-Scan. Bestehende Admin-Konten behalten Namen und Passwort.
- Web-App-Anpassungen: Berechtigung `access-admin`, Adressen `/admin/login`, `/login`, `/dashboard`, `/artisan-runner-ui`; keine API-/Sanctum-Endpunkte oder Stateful-Konfiguration ergänzt. Zusätzliche feste Schritte für `npm ci --ignore-scripts` und `npm run build` in CLI und Browser. Node/npm-Erkennung nutzt unter Windows vorhandene Programme ohne frei eingegebene Shellbefehle.
- Browser entspricht dem Referenzablauf mit fünf Sprachen, explizitem Ausführen/Überspringen, Navigation, Aktivitäts-/Laufzeitanzeige und verborgenem Prozessoutput. Administratorpasswörter werden per STDIN übertragen. CSRF, Tokenzugang, HTTPS außerhalb Loopback, Login-Begrenzung, Parallelitätssperre und Abschlusssperre bleiben wirksam.
- Neue ausdrückliche Freischaltung über `.setup-access`; Abschluss erstellt `.setup-complete` und entfernt den Zugangsschlüssel. Alte Sperre `storage/app/web-setup.complete` wird weiterhin geprüft. `web-setup.local.php` und die frühere Beispielkonfiguration sind durch die neue Anleitung ersetzt; keine lokale Zugangskonfiguration automatisch übernommen oder verändert.
- Setup-Dokumentation, README, Installationsübersicht und Befehlsübersicht aktualisiert. Die vorstehenden historischen Setup-Einträge beschreiben den ersetzten Stand; aktueller Ablauf steht in [docs/setup.md](docs/setup.md). Wie in LaravelApi keine automatische Umgebungswahl/Produktionsumstellung oder `SETUP.md`-Erzeugung mehr.
- Prüfungen ausschließlich mit temporären Setup-Fixtures, simulierten Unterbefehlen und isolierter Testdatenbank. Entwickler-`.env` und SQLite-Datei nicht als Setup-Ziel verwendet; Hash-Prüfung bestätigt deren unveränderten Inhalt. `AGENTS.md` unverändert.
- Wichtige Betriebsschritte: Zur tatsächlichen Einrichtung `composer setup` beziehungsweise `php setup.php` verwenden oder den Browser bewusst mit eigenem Schlüssel freischalten. Vor Produktion `APP_ENV`/`APP_DEBUG`, HTTPS und Entwicklungspakete gesondert abstimmen; Hosting benötigt für Browser-Schritte `proc_open` und die nötigen CLI-Programme.
- Abschlussprüfung: vollständige Testsuite mit 53 Tests und 730 Assertions bestanden. Pint, PHP-Syntaxprüfung, JavaScript-Syntaxprüfung, Composer-Validierung, Dokumentationslinks und Git-Diff-Prüfung erfolgreich. Neuer Browser-Test verwendet auch für native PHP-Sessions sein temporäres Fixture-Verzeichnis. Keine Anwendungsmigrationen oder tatsächliche Einrichtung ausgeführt; keine neuen Abhängigkeiten installiert.
