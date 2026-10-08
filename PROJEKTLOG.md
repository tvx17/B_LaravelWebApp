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

## Wichtige offene Aufgaben

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
