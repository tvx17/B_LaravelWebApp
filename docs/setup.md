# Einrichtung nach dem Klonen

Ohne Shell-Zugriff steht ein [geschützter Browser-Assistent](browser-setup.md) unter `public/setup.php` zur Verfügung.

Im Projektverzeichnis starten:

```sh
php setup.php
```

Der Einstieg benötigt kein vorhandenes `vendor/` und ist unabhängig von Composer startbar. PHP >= 8.3 und Composer müssen installiert sein; für Frontend-Assets zusätzlich Node.js/npm. SQLite benötigt `pdo_sqlite`, MariaDB/MySQL benötigen `pdo_mysql`. Der Datenbankserver muss bereits laufen. Systemprogramme installiert der Assistent nicht.

Jeder ausführende Schritt wird bestätigt, standardmäßig mit Nein. Bei vorhandener `.env` wird diese weiterverwendet. Die Vorlage übernimmt den lokalen Projektstand mit aktivierter Artisan Runner UI, ohne Zugangsdaten, Anwendungsschlüssel oder den rechnergebundenen Kopia-Pfad. Ein vorhandener Anwendungsschlüssel wird nicht ersetzt.

1. Entwicklung oder Produktion auswählen und gegebenenfalls `.env` erstellen.
2. PHP-Abhängigkeiten aus dem Lockfile installieren; Produktion ohne Entwicklungsabhängigkeiten.
3. Umgebung, Debug-Werkzeuge und Basis-URL konfigurieren; fehlenden Anwendungsschlüssel erzeugen.
4. SQLite, MariaDB oder MySQL konfigurieren. SQLite-Datei bzw. Serverdatenbank auf Wunsch erstellen. Die Serverdatenbank wird mit den angegebenen Zugangsdaten erstellt; dafür ist CREATE-Recht erforderlich. Server und Datenbankbenutzer werden nicht installiert oder angelegt.
5. CORS-Origins und Cookie-Unterstützung abfragen. Origins enthalten Schema, Host und optional Port, keine Pfade. Aktuelle CORS-Pfade: `api/*` und `sanctum/csrf-cookie` in `config/cors.php`. Diese Konfiguration erstellt keine API-Endpunkte.
6. Offene Migrationen anwenden; vorhandene Tabellen nicht zurücksetzen. Der bestehende Testdaten-Seeder wird nicht ausgeführt.
7. Einen Benutzer mit beiden Rechten `access-admin` und `run-artisan-runner-ui` erstellen; Passwort verdeckt eingeben und bestätigen. Vorhandene Benutzer werden nicht überschrieben. Die Runner-Oberfläche bleibt gemäß ihrer Konfiguration auf die lokale Umgebung beschränkt.
8. npm-Abhängigkeiten installieren, Frontend bauen und optional den Storage-Link anlegen. Composer führt bereits Package Discovery und Filament-Asset-Aktualisierung aus. Die übrigen Pakete sind im Repository konfiguriert und ihre Tabellen Teil der Migrationen. Kopia bleibt separat.
9. URLs und Startbefehle anzeigen und auf Wunsch in `SETUP.md` speichern. Der Bericht enthält keine Zugangsdaten und wird von Git ignoriert.

Nein überspringt den jeweiligen Schritt. Fehlen Voraussetzungen, werden abhängige Schritte erklärt übersprungen. Bei einem Fehler stoppt die Einrichtung; nach Korrektur erneut starten. Bereits erledigte Schritte können übersprungen werden. Eine vorhandene Konfiguration wird nur bei bestätigter Neukonfiguration geändert. Hierbei wird auch der Laravel-Konfigurationscache geleert.

Es werden keine Serverprozesse gestartet. In Produktion muss ein Webserver auf `public/` zeigen; Queue-Worker und Scheduler werden über die Betriebssystemdienste eingerichtet. Die Ausgabe zeigt die konfigurierte Basis-URL; `php artisan serve` startet standardmäßig einen lokalen Entwicklungsserver auf Port 8000.

`composer setup` ist ein alternativer Einstieg in denselben Dialog.
