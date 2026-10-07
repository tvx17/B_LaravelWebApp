# MariaDB vorbereiten und aktivieren

Laravel enthält bereits die Verbindung `mariadb` in `config/database.php`. Sie verwendet `utf8mb4`, `utf8mb4_unicode_ci` und den strikten SQL-Modus. Die lokale Anwendung verwendet bis zum Wechsel SQLite. PHP benötigt `pdo_mysql`; diese Erweiterung ist auf der aktuellen Entwicklungsmaschine vorhanden.

## Späterer Wechsel

1. Einen MariaDB-Server bereitstellen und die Datenbank `laravel_webapp` mit `utf8mb4` und `utf8mb4_unicode_ci` anlegen. Einen eigenen Benutzer mit Rechten ausschließlich auf diese Datenbank verwenden; Migrationen benötigen auch Rechte zum Anlegen und Ändern von Tabellen.
2. Den Datenbankblock in der lokalen `.env` ersetzen:

```dotenv
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_webapp
DB_USERNAME=laravel_webapp
DB_PASSWORD="hier-das-lokale-passwort-eintragen"
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

Eine vorhandene `DB_URL` entfernen, damit sie die einzelnen Verbindungswerte nicht überschreibt. Bei einem entfernten Server TLS entsprechend der Serverkonfiguration einrichten; Laravel unterstützt `MYSQL_ATTR_SSL_CA`.

3. Im Projektverzeichnis ausführen:

```powershell
php artisan config:clear
php artisan migrate
php artisan migrate:status
php artisan permission:cache-reset
```

Die Migrationen erstellen auch Spatie-Berechtigungen sowie die Tabellen für Sessions, Cache und Queues. Vorhandene SQLite-Daten werden dabei nicht übertragen. Zugangsdaten gehören nur in lokale, ignorierte Umgebungsdateien.

## Tests gegen MariaDB

Die normalen Tests bleiben mit `php artisan test` auf SQLite im Arbeitsspeicher. Für MariaDB steht `phpunit.mariadb.xml` bereit.

Eine separate Datenbank `laravel_webapp_test` und einen eigenen Testbenutzer anlegen. `.env` nach `.env.testing` kopieren und dort Host, Port, Benutzer und Passwort für die Testdatenbank setzen. `APP_ENV=testing`, `APP_DEBUG=true` und `DB_DATABASE=laravel_webapp_test` eintragen. Die Datei `.env.testing` ist ignoriert.

```powershell
php artisan config:clear
php artisan test --configuration=phpunit.mariadb.xml
```

Die Testkonfiguration erzwingt `mariadb`, einen leeren `DB_URL` und den Datenbanknamen `laravel_webapp_test`. `RefreshDatabase` kann Tabellen in dieser Testdatenbank löschen und neu anlegen; der Testbenutzer sollte ausschließlich darauf zugreifen dürfen. Die tatsächliche MariaDB-Verbindung wird nach Bereitstellung des Servers geprüft.

Referenz: [Laravel-Datenbankdokumentation](https://laravel.com/framework/docs/database).
