# Bewertung von Migrationsgeneratoren

Stand: 2026-10-13. Geprüft wurden das angefragte `xethron/migrations-generator` und die Weiterentwicklung `kitloong/laravel-migrations-generator` für diese Laravel-13-Installation.

## Ergebnis

**Kitloong 7.4.0 ist auf Wunsch als Entwicklungsabhängigkeit für spätere Apps installiert. Xethron wird nicht verwendet.** Die vorhandenen sechs Migrationen beschreiben bereits Benutzer, Sessions, Cache, Jobs, Berechtigungen, Telescope und Medien. Neue fachliche Tabellen sollten weiterhin mit regulären Laravel-Migrationen entstehen.

Ein Reverse-Engineering-Generator wird interessant, wenn eine vorhandene, unabhängig entstandene Datenbank ohne entsprechende Laravel-Migrationen übernommen werden soll. Er ist kein Datenbackup, kein Datentransfer und kein automatischer Ersatz für das Prüfen von Schemaänderungen. Für den vorgesehenen SQLite-/MariaDB-Wechsel ist die Anwendung vorhandener Migrationen der reguläre Ablauf.

## Wartung und Kompatibilität

| Paket | Prüfergebnis |
|---|---|
| `xethron/migrations-generator` | Letzter Commit auf dem Standardbranch: **2017-09-19**; letzte stabile Version **2.0.2** vom selben Tag. README beschreibt Laravel 4/5. Composer-Abhängigkeiten enthalten `doctrine/dbal ~2.4` und `xethron/laravel-4-generators ~3.1.0`. Kein dokumentierter Laravel-13-Support. |
| `kitloong/laravel-migrations-generator` | Gepflegte Weiterentwicklung des Xethron-Projekts. Letzter Commit auf dem Standardbranch und Version **7.4.0**: **2026-05-10**. Paketanforderungen: PHP `^8.2`, PDO und Illuminate `^11.0\|^12.0\|^13.0`; README nennt unter anderem SQLite und MariaDB. |

Die Commit-Daten wurden direkt über GitHubs API geprüft. Ein aktuelles Packagist-Metadaten-Update bedeutet keine neue Paketversion. Das Alter allein wäre kein Ausschlussgrund; hier kommen veraltete Zielversionen und Abhängigkeiten hinzu. Eine theoretisch permissive Composer-Bedingung wie `illuminate/support >=4.1` ist kein Nachweis für Funktion unter Laravel 13/PHP 8.5.

## Vorgehen bei späterem Bedarf

Composer-Auflösung und eine tatsächliche SQLite-Erzeugung wurden unter Windows mit Laravel 13 erfolgreich geprüft. Für eine neue Bestandsdatenbank den Ablauf zunächst mit einer isolierten Datenbankkopie durchführen. Generierte Migrationen in einem separaten Verzeichnis ausgeben, Tabellen-/Index-/Fremdschlüsseldefinitionen prüfen und anschließend gegen eine neue leere Testdatenbank ausführen. Keine doppelten Create-Migrationen in die bestehende Historie übernehmen; insbesondere keine ungeprüften Einträge in der Migrationstabelle erzeugen.

## Aufruf und Konfiguration

```powershell
php artisan help migrate:generate
# Beispiel für eine ausdrücklich ausgewählte, konfigurierte Datenbankverbindung:
php artisan migrate:generate --connection=legacy --tables=customers,orders --path=database/generated-migrations --skip-log
```

`legacy`, Tabellen und Ausgabeverzeichnis sind Beispiele. Die Verbindung muss zuvor mit den tatsächlichen Zugangsdaten privat konfiguriert werden. `--skip-log` verhindert Einträge in der Migrationstabelle. Der Befehl erzeugt Dateien; deren Anwendung auf einer anderen Datenbank ist ein gesonderter Schritt. Nicht ohne Zielprüfung gegen die vorhandene Anwendung ausführen.

Der Provider wird automatisch erkannt. Es gibt keinen Web-Endpunkt. Bei `composer install --no-dev` ist das Paket nicht verfügbar. Es benötigt keine Linux-spezifischen Werkzeuge; SQLite wurde nativ unter Windows getestet, MariaDB-Erzeugung mangels eingerichteter Testinstanz noch nicht.

Defaults unter dem Konfigurations-Namespace `migrations-generator` stammen aus dem Paket; keine zusätzliche Projekt-Konfigurationsdatei ist erforderlich:

| Schlüssel | Standard |
|---|---|
| `migration_template_path` | Paketdatei `stubs/migration.generate.stub` |
| `migration_target_path` | `database/migrations` im Projekt; für Imports mit `--path` überschreiben |
| `filename_pattern.table` | `[datetime]_create_[name]_table.php` |
| `filename_pattern.view` | `[datetime]_create_[name]_view.php` |
| `filename_pattern.procedure` | `[datetime]_create_[name]_proc.php` |
| `filename_pattern.foreign_key` | `[datetime]_add_foreign_keys_to_[name]_table.php` |

Der vorhandene Setup-Assistent installiert die Entwicklungsabhängigkeit über Composer und registriert das Paket mit Package Discovery. Keine automatische Generator-Aktion ergänzt: Einrichtung wendet die gepflegten Projektmigrationen an und soll das Schema nicht erneut rückwärts generieren. Keine Änderungen an der Entwicklerdatenbank vorgenommen.

Quellen: [Xethron-README](https://github.com/Xethron/migrations-generator), [Xethron letzter Commit](https://github.com/Xethron/migrations-generator/commit/a05bd7319ed808fcc3125212e37d30ccbe0d2b8b), [Xethron-Paketdaten](https://packagist.org/packages/xethron/migrations-generator), [Kitloong-README](https://github.com/kitloong/laravel-migrations-generator), [Kitloong letzter Commit](https://github.com/kitloong/laravel-migrations-generator/commit/706366afcef5be8a01a92c3a12bfa04c01a1eed2), [Kitloong-Paketdaten](https://packagist.org/packages/kitloong/laravel-migrations-generator).
