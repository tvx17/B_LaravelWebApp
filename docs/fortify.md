# Laravel Fortify

Fortify 1.41 übernimmt die Authentifizierung des Blade-Frontends. Die Konfiguration liegt in `config/fortify.php`, die View-Zuordnung und Login-Begrenzung in `app/Providers/FortifyServiceProvider.php`. Der Provider ist in `bootstrap/providers.php` registriert; das Paket selbst wird automatisch erkannt.

## Aktive Funktionen

- `GET /login` zeigt das bestehende Formular; `POST /login` prüft E-Mail und Passwort über den Session-Guard `web`.
- Erfolgreiche Anmeldung führt zur ursprünglich angeforderten Seite oder `/dashboard`. Maximal sechs Login-Anfragen pro Minute und IP sind erlaubt. Fehlerhafte Zugangsdaten werden als Formularfehler zurückgegeben.
- `POST /logout` beendet die Anmeldung, invalidiert die Session und führt zurück zu `/login`.
- `/user/confirm-password` bietet die Passwortbestätigung für spätere Funktionen mit `password.confirm`-Middleware.

Fortify verwaltet die Auth-Routen. Keine parallelen Login-/Logout-Routen in `routes/web.php` ergänzen. Der frühere `SessionController` wurde entfernt. Filament behält seine eigene Login-Seite und verwendet dieselben Benutzer und denselben `web`-Guard; Spatie-Berechtigungen gelten weiterhin.

Benutzer werden weiterhin interaktiv erstellt:

```powershell
php artisan app:create-user deine-adresse@example.com --name="Dein Name"
```

Für Panel- oder Runner-Zugriff die entsprechenden Optionen `--admin` und/oder `--runner` verwenden. Eine Anmeldung vergibt keine zusätzlichen Berechtigungen.

## Spätere Erweiterungen

Die optionale `features`-Liste ist zunächst leer. Registrierung, Passwort-Reset, E-Mail-Verifikation, Profiländerungen, Zwei-Faktor-Anmeldung und Passkeys müssen mit passenden Views, Actions, Konfiguration und Tests gezielt aktiviert werden. Vor Passwort-Reset oder Verifikation Mailversand konfigurieren. Zwei-Faktor-Anmeldung benötigt zusätzliche User-Spalten und das vorgesehene Trait; Passkeys benötigen ihre eigene Datenbankstruktur und HTTPS-Konfiguration. Die mitgelieferten Funktionen sind durch die Paketinstallation allein nicht aktiv.

Für die aktuelle Integration sind keine neuen Datenbankspalten oder Migrationen erforderlich. Das Paket läuft unter Windows.

Prüfung: `php artisan test`. Referenz: [Laravel Fortify](https://github.com/laravel/fortify).
