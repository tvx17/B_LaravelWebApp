# Filament Admin-Panel

Filament stellt das Admin-Panel unter `/admin` mit eigener Login-Seite `/admin/login` bereit. Es verwendet das bestehende User-Modell und den Session-Guard `web`. Die Anmeldung des bisherigen Blade-Frontends bleibt bestehen.

## Zugriff

Panelzugriff erfordert die Spatie-Berechtigung `access-admin`, auch in der lokalen Umgebung. Einen Benutzer mit dieser Berechtigung interaktiv anlegen:

```powershell
php artisan app:create-user deine-adresse@example.com --name="Dein Name" --admin
```

Das Passwort wird verdeckt abgefragt und bestätigt. `--runner` kann zusätzlich angegeben werden, wenn der Benutzer auch die Artisan Runner UI bedienen soll. Für einen bestehenden Benutzer `Permission::findOrCreate('access-admin', 'web')` anlegen und über `givePermissionTo()` oder eine vorgesehene Rolle zuweisen. Es werden keine voreingestellten Administratoren oder Passwörter erstellt.

Die Filament-Anmeldung prüft Panelzugriff über `User::canAccessPanel()`. Das Admin-Panel enthält zunächst nur das Dashboard; fachliche Resources, Formulare und Verwaltungsfunktionen werden später festgelegt. Die Panelberechtigung ersetzt keine Policies für spätere CRUD-Aktionen. Das Panel verwendet strikte Autorisierung, damit fehlende Policies nicht versehentlich Zugriff gewähren.

## Assets und Weiterentwicklung

Der Panel-Provider liegt in `app/Providers/Filament/AdminPanelProvider.php` und ist in `bootstrap/providers.php` registriert. Filament verwendet seine eigenen veröffentlichten Assets; für das Standard-Panel ist kein zusätzliches Vite-Theme nötig. Nach Paketupdates `php artisan filament:upgrade` ausführen; der Befehl ist im Composer-Script `post-autoload-dump` berücksichtigt.

Das Basispaket enthält keine fachlichen Verwaltungsseiten oder zusätzliche Plugins für Socialite, Media Library oder Rollenverwaltung. Diese Integrationen gezielt ergänzen, sobald die Anforderungen vorliegen. Für lokale Konfigurationen nach Änderungen `php artisan config:clear` ausführen.

Referenz: [Filament-Installation](https://filamentphp.com/docs/5.x/introduction/installation).
