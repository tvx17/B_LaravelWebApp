# Einrichtung im Browser ohne Shell-Zugriff

Der eigenständige Einstieg ist `public/setup.php`. Er benötigt noch kein `vendor/`. Die Dateien im Repository gehören ebenfalls zur Einrichtung; den Einstieg nicht allein hochladen.

## Aktivieren

1. Projekt auf das Hosting hochladen. Die Dokumentwurzel der Domain muss auf `public/` zeigen. `.env`, `vendor/` und die Zugangskonfiguration bleiben außerhalb der öffentlich erreichbaren Dokumentwurzel.
2. `web-setup.example.php` lokal zu `web-setup.local.php` kopieren. Im Editor einen eigenen zufälligen Zugangscode mit mindestens 32 Zeichen eintragen, beispielsweise aus dem Passwortgenerator eines Passwortmanagers. Diese Datei im Projektstamm hochladen, nicht in `public/`. Sie ist von Git ausgeschlossen.
3. PHP >= 8.3, Sessions, die nötigen PDO-Treiber und Schreibrechte für `.env`, `bootstrap/cache`, `storage` und gegebenenfalls `database` bereitstellen. Nur PHP-Versionen verwenden, welche die installierten Lockfile-Abhängigkeiten erfüllen.
4. `https://deine-domain.example/setup.php` öffnen und den Zugangscode eingeben. Er wird nicht über die URL übertragen. Jeder Schritt bietet „Ja, Schritt durchführen“ und „Nein, überspringen“. Bei einem Fehler bleibt der Schritt offen, mit „Zurück“ lassen sich Einstellungen korrigieren.
5. Zum Abschluss die Übersicht bei Bedarf als `SETUP.md` speichern. Der Assistent erzeugt eine Sperrdatei `storage/app/web-setup.complete`. Anschließend `public/setup.php` und `web-setup.local.php` per FTP oder Hosting-Dateiverwaltung entfernen.

Eine Aktivierung ist nur durch die separate Zugangskonfiguration möglich. Das unveränderte Repository stellt keinen offenen Installer bereit. HTTPS ist erforderlich, außer bei Zugriff von der lokalen Loopback-Adresse. Hinter einem Reverse Proxy muss der Webserver den tatsächlichen HTTPS-Status über `HTTPS=on` an PHP übergeben. Der Installer vertraut keinem vom Browser gelieferten Forwarded-Header.

## Hosting ohne Prozessausführung

Migrationen, Benutzeranlage und Storage-Link laufen direkt in PHP und benötigen keine Shell. Die PHP-Abhängigkeiten müssen dafür schon vorhanden sein. Composer/npm benötigen ausführbare Programme; PHP allein ersetzt diese nicht.

Auf einem lokalen Rechner im geklonten Projekt vorbereiten:

```sh
composer install --no-dev --optimize-autoloader
npm ci --ignore-scripts
npm run build
```

Dann `vendor/`, `public/build/` sowie die durch Composer erzeugten Filament-Dateien in `public/css/filament`, `public/js/filament` und `public/fonts/filament` hochladen, soweit vorhanden. Plattformanforderungen der Ziel-PHP-Installation müssen erfüllt sein. Die lokale `.env` und lokale Datenbank nicht hochladen. In der Browser-Einrichtung Composer, npm und Build überspringen und die übrigen Schritte durchführen. Für Entwicklung Composer ohne `--no-dev` ausführen.

## Hosting mit Prozessausführung

Falls der Anbieter dies erlaubt, in `web-setup.local.php` `allow_processes` auf `true` setzen und gegebenenfalls die Programmpfade für Composer und npm hinterlegen. Die Einträge sind Argumentlisten; beispielsweise `['/usr/bin/php', '/home/account/composer.phar']`. Unter Windows bei Batch-Startern gegebenenfalls `['cmd.exe', '/c', 'npm.cmd']` verwenden. Programmnamen oder Befehle können im Browser nicht eingegeben werden.

Installationsschritte können Hosting-Zeit-, Speicher- oder Netzwerklimits überschreiten. Der Assistent begrenzt den eigenen Prozess auf 90 Sekunden; kürzere Hosting-Limits haben Vorrang. Dann die Dateien lokal vorbereiten und hochladen. Ausgaben externer Programme und Datenbank-Ausnahmen werden nicht im Browser angezeigt, damit Zugangsdaten nicht offengelegt werden.

## Wiederaufnahme und Betrieb

Vor dem Abschluss bleibt der Assistent mit dem Zugangscode erneut zugänglich. Die Sitzung läuft nach einer Stunde ab; beim erneuten Anmelden kann die Einrichtung wieder durchlaufen werden. Bestehende `.env`, Anwendungsschlüssel und Benutzer werden nicht überschrieben. Übersprungene Schritte stehen in der Abschlussübersicht. Ein Abschluss bestätigt das Ende des Assistenten, keine vollständige Produktionsbereitschaft.

Für eine bewusst erneute Einrichtung die entfernten Dateien neu hochladen, den Zugangscode wechseln und die Sperrdatei per Dateiverwaltung löschen. Änderungen gelten für die vorhandene Anwendung und Datenbank.

Der Browser startet keine dauerhaften Server, Worker oder Scheduler. Auf dem Hosting den Scheduler als Cronjob (`php artisan schedule:run` einmal pro Minute) einrichten und Queue-Worker über den Anbieter betreiben. Kopia bleibt separat. Die URLs in der Übersicht stammen aus der Basis-URL und den registrierten Laravel-Routen; DNS, TLS und Webserver werden dadurch nicht eingerichtet.
