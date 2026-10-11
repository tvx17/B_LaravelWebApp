# Einrichtung nach dem Klonen

Das Setup folgt dem Ablauf des benachbarten Projekts `LaravelApi`, angepasst an diese Blade-Web-App. Start im Projektverzeichnis:

```powershell
composer setup
# alternativ, auch ohne vendor/:
php setup.php
php scripts/Setup.php
```

Der Composer-Callback übernimmt die tatsächliche Konsoleneingabe, damit Bestätigungen unter Windows funktionieren. Kein `vendor/`, keine `.env` und keine vorhandene Laravel-Session werden für den Einstieg benötigt. PHP ab 8.3 und Composer müssen installiert sein; für Frontend-Assets außerdem Node.js/npm. SQLite benötigt `pdo_sqlite`, MariaDB/MySQL benötigen `pdo_mysql` und einen erreichbaren Server.

## Ablauf

Jeder ausführende CLI-Schritt benötigt eine Bestätigung; Enter bedeutet Nein. Bei einem Fehler endet die Einrichtung. Bereits bestätigte Änderungen bleiben bestehen, und der Assistent kann erneut gestartet werden.

1. Composer-Abhängigkeiten aus dem Lockfile einschließlich Entwicklungspaketen installieren, zunächst ohne Laravel-Hooks.
2. Fehlende `.env` aus der Projektvorlage erstellen. Vorhandene Werte und Schlüssel bleiben erhalten.
3. Konfigurations- und Routencache leeren. Einen fehlenden Anwendungsschlüssel erst nach Bestätigung erzeugen.
4. SQLite, MariaDB oder MySQL auswählen; neue Datenbank anlegen oder vorhandene verwenden. Verbindung testen und die Speicherung separat bestätigen. Bestehende SQLite-Dateien werden nicht überschrieben. Eine Server-Neuanlage benötigt `CREATE DATABASE`-Rechte; der Server und Datenbankbenutzer werden nicht installiert.
5. Anwendungs-URL und optionalen Frontend-Origin konfigurieren. Für das bestehende Blade-Frontend dieselbe Adresse verwenden; dieser Schritt ergänzt keine API-/Sanctum-Endpunkte. Origins enthalten Schema, Host und optional Port, keine Pfade oder Zugangsdaten. Für Cookiezugriff bleibt die gleiche Host-/Schema-Prüfung wie im API-Setup bestehen. CORS betrifft nur die Pfade in `config/cors.php`.
6. Migrationen für das angezeigte Datenbankziel bestätigen. Es werden weder `fresh`/`reset` noch Testdaten-Seeder ausgeführt.
7. Package Discovery, Filament-Assets und Permission-Cache vorbereiten.
8. Optionalen Administrator anlegen oder einem bestehenden Benutzer `access-admin` und `run-artisan-runner-ui` geben. Name und Passwort bestehender Konten bleiben erhalten; neue Passwörter werden verdeckt eingegeben und bestätigt. Separat: `php artisan setup:admin`.
9. Frontend-Abhängigkeiten mit `npm ci --ignore-scripts` installieren und Vite-Assets mit `npm run build` bauen.
10. Runner-Aktivierung, Storage-Link und lokalen Brain-Scan bestätigen.

Zum Schluss erscheinen die tatsächlichen Web-App-Adressen (`/`, `/login`, `/dashboard`, `/admin/login`, `/artisan-runner-ui`) sowie lokale Telescope-/Brain-Adressen. Der bisherige `app:setup`-Befehl wird durch den eigenständigen Einstieg ersetzt: Der Assistent startet vor dem Laravel-Bootstrap, und nach Konfigurationsänderungen laden neue Artisan-Prozesse die aktualisierten Werte.

## Umgebung und Betrieb

Wie im API-Projekt verändert der Assistent `APP_ENV` und `APP_DEBUG` nicht automatisch. Vor Produktion die Umgebung, Debug-Werkzeuge, HTTPS und Cookies gesondert einstellen. Der CLI-Assistent installiert grundsätzlich Entwicklungspakete; eine Produktionsauslieferung mit `composer install --no-dev` anschließend bewusst planen.

Server, Queue, Scheduler und Backup werden nicht automatisch gestartet. Die öffentliche Dokumentwurzel muss `public/` sein. PHP-CLI/Composer werden über Servervariablen `SETUP_PHP_BINARY` und `COMPOSER_BINARY` oder geeignete vorhandene Installationspfade gefunden; Composer muss eine PHP-Datei beziehungsweise `composer.phar` sein. Node.js/npm müssen im PATH des ausführenden Benutzers verfügbar sein. Pail benötigt PCNTL und läuft unter Windows nicht nativ.

## Einrichtung im Browser

Der unabhängige Einstieg `/setup.php` bietet denselben Kernablauf in English (Standard), Deutsch, Français, Italiano und Español. Er verwendet die Struktur aus LaravelApi: Actions in `app/Actions/Setup/`, Template in `resources/views/setup/wizard.php`, Übersetzungen in `resources/setup/translations.json`, Statusanzeige in `public/setup-progress.js`.

1. Projekt auf einen laufenden PHP-Webserver übertragen; Document Root auf `public/` setzen.
2. Im Projektstamm eine Git-ignorierte Datei `.setup-access` erstellen. Als einzigen Inhalt einen eigenen zufälligen Schlüssel mit mindestens 32 Zeichen aus einem Passwortmanager verwenden. Datei außerhalb von `public/` belassen und Zugriff auf den Serverbenutzer begrenzen.
3. `/setup.php` öffnen, Sprache auswählen und Schlüssel eingeben. Externe Zugriffe benötigen HTTPS; lokale Loopback-Zugriffe dürfen HTTP verwenden. Forwarded-Header werden nicht ungeprüft vertraut.
4. Schritte über „Schritt ausführen“ ausdrücklich starten oder überspringen. Konto-Neuanlage erfordert zusätzlich die entsprechende Auswahl. SQLite-Dateien sind im Browser auf `.sqlite` innerhalb von `database/` beschränkt; bestehende Dateien bleiben erhalten.
5. In der Übersicht „Abschließen und sperren“ wählen. `.setup-complete` wird angelegt und `.setup-access` entfernt. Eine neue Zugangsschlüsseldatei hebt die Sperre nicht auf.

Der Browser benötigt für Composer, npm und Artisan PHP-Prozessausführung mit `proc_open`, temporären Speicher, passende CLI-Erweiterungen und Schreibrechte. Anders als der frühere Browser-Installer führt er Artisan-Schritte wie LaravelApi als feste Unterprozesse aus. Falls das Hosting dies verbietet, Abhängigkeiten und Assets lokal vorbereiten; vollständige Browser-Einrichtung ist dort nicht möglich. Passwörter der Administratoranlage werden über STDIN übertragen, nicht in Prozessargumenten. Datenbankpasswörter werden nicht als Formularwerte angezeigt.

Eigene PHP-Sessions mit CSRF-Schutz, einstündigem Zugang, Rate-Limit für falsche Schlüssel und Dateisperre schützen den Ablauf. Prozessausgaben bleiben im Browser verborgen; feste Fehlermeldungen nennen bekannte Ursachen. JavaScript zeigt Aufgabe, Laufzeit, Aktivitätsmeldungen und Zeit ohne Prozessausgabe. Ohne JavaScript funktionieren die Formulare weiterhin. Bei Verbindungsabbruch nicht erneut absenden; die serverseitige Aufgabe kann weiterlaufen.

## Wechsel vom bisherigen Web-App-Setup

`web-setup.local.php` dient nicht mehr zur Freischaltung oder Programmauswahl. Eine neue Einrichtung wird ausdrücklich über `.setup-access` aktiviert. Die bisherige Abschlusssperre `storage/app/web-setup.complete` wird weiterhin beachtet, damit ein abgeschlossener Installer durch den Umbau nicht freigeschaltet wird. Für eine bewusst erneute Einrichtung sämtliche zutreffenden Abschlusssperren manuell entfernen und einen neuen Zugangsschlüssel erstellen.

Die bisherige automatische `SETUP.md`-Berichterzeugung ist durch die Abschlussübersicht wie im API-Projekt ersetzt. `.env`, Zugangsschlüssel und Datenbank werden durch die Umstellung nicht verändert. Neue Runtime-Dateien `.setup-access`, `.setup-complete`, `.setup.lock` und `.setup-login-attempts.json` sind Git-ignoriert.

Socialite-Anbieter, fachliche Medienmodelle, Kopia-Repository, Aufbewahrung und Wiederherstellungstest bleiben gesonderte Aufgaben. Details: [Installationsübersicht](installationsuebersicht.md), [Kopia](kopia.md).
