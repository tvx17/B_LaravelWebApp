# Artisan Runner UI

`codesquirrel/artisan-runner-ui` stellt unter `/artisan-runner-ui` eine Blade-Oberfläche zum Ausführen freigegebener Artisan-Befehle bereit. Zusätzliche Frontend-Builds oder Migrationen sind nicht erforderlich.

In der aktuellen lokalen `.env` ist die UI aktiviert. Weitere Installationen aktivieren sie mit `ARTISAN_RUNNER_UI_ENABLED=true` und anschließend `php artisan config:clear`. Der Pfad lässt sich mit `ARTISAN_RUNNER_UI_PATH` ändern.

## Zugriff

Die UI ist ausschließlich in der Umgebung `local` verfügbar. Sie verlangt einen angemeldeten Benutzer mit der Spatie-Berechtigung `run-artisan-runner-ui` (Guard `web`). Gäste werden zur Login-Seite `/login` weitergeleitet. Nach der Anmeldung führt `/dashboard` zur Runner UI und bietet die Abmeldung an.

Zusätzlich verlangt die UI seit 2026-10-13 eine bestätigte E-Mail-Adresse. Unverifizierte Benutzer werden zur Bestätigungsseite geleitet beziehungsweise erhalten bei JSON-Aufrufen 403. Rechte allein reichen nicht aus. [Verifikation und Mailkonfiguration](email-verification.md).

Einen Benutzer interaktiv anlegen:

```powershell
php artisan app:create-user deine-adresse@example.com --name="Dein Name" --runner
```

Das Passwort wird verdeckt abgefragt und bestätigt (mindestens zwölf Zeichen). Ohne `--runner` wird ein Benutzer ohne Runner-Berechtigung angelegt. Bestehende Benutzer werden nicht überschrieben. Es gibt keine öffentlichen Registrierungsrouten oder voreingestellten Zugangsdaten.

Die Berechtigung später einem vorgesehenen Benutzer oder einer Rolle zuweisen, beispielsweise in einem Seeder:

```php
use Spatie\Permission\Models\Permission;

Permission::findOrCreate('run-artisan-runner-ui', 'web');
$user->givePermissionTo('run-artisan-runner-ui');
```

## Freigegebene Befehle

`config/artisan-runner-ui.php` erlaubt zunächst `about`, `migrate:status`, `permission:show` und `permission:cache-reset`. Die Sperrliste des Pakets bleibt zusätzlich aktiv. Weitere Befehle ausdrücklich in `allowed` ergänzen. Ausführungen werden vom Paket mit Benutzer, Befehl und Parametern im Laravel-Log protokolliert.

Die Tests prüfen Zugriffsschutz, Umgebungsbeschränkung, erfolgreiche Ausführung und die Ablehnung nicht freigegebener Befehle: `php artisan test`.

Paket: https://github.com/reyesorlan/artisan-runner-ui
