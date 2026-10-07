# Rollen und Berechtigungen

Spatie Laravel Permission ist mit dem Standard-Guard `web` eingerichtet. Die Konfiguration liegt in `config/permission.php`; `App\Models\User` verwendet `HasRoles`. Rollen und Berechtigungen werden bei Bedarf angelegt; die Installation erstellt keine Standardrollen.

## Verwendung

```php
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$permission = Permission::findOrCreate('articles.edit', 'web');
$role = Role::findOrCreate('editor', 'web');
$role->givePermissionTo($permission);
$user->assignRole($role);

$user->can('articles.edit');
```

Prüfungen in Blade verwenden Laravels `@can('articles.edit')` und `@endcan`. In Controllern kann `Gate::authorize('articles.edit')` verwendet werden. Berechtigungsprüfungen müssen serverseitig erfolgen; ausgeblendete Bedienelemente allein schützen keine Aktionen.

Für Routen stehen `role`, `permission` und `role_or_permission` zur Verfügung:

```php
Route::middleware(['auth', 'permission:articles.edit'])->group(function () {
    // Geschützte Routen ergänzen.
});
```

## Einrichtung und Prüfung

- `php artisan migrate`: Berechtigungstabellen auf weiteren Installationen anlegen.
- `php artisan permission:cache-reset`: Berechtigungscache bei Bedarf zurücksetzen.
- `php artisan test`: Tests einschließlich Rollenvererbung, Entzug direkter Berechtigungen und Zugriffsschutz ausführen.

Die Paketinstallation umfasst keine Login-Oberfläche. Authentifizierung und das konkrete Rollenmodell werden separat festgelegt.

Dokumentation: https://spatie.be/docs/laravel-permission/v8/introduction
