# Laravel Socialite

Laravel Socialite ist als reguläre Composer-Abhängigkeit installiert und wird automatisch von Laravel registriert. Das Paket benötigt keine Linux-spezifischen Erweiterungen und lässt sich in der vorhandenen Windows-PHP-Umgebung verwenden.

Die konkrete OAuth-Anmeldung wird eingerichtet, sobald der Anbieter feststeht. Aktuell sind keine Social-Login-Routen, Login-Schaltflächen oder Zugangsdaten konfiguriert. Die bestehende Anmeldung per E-Mail und Passwort bleibt verfügbar.

## Anbieter später konfigurieren

Beim Anbieter eine OAuth-Anwendung anlegen und Client-ID, Client-Secret und eine exakt passende Callback-URL festlegen. Zugangsdaten nur in der lokalen `.env` oder Secret-Verwaltung hinterlegen. Anschließend den Anbieterblock in `config/services.php` ergänzen, beispielsweise für GitHub:

```php
'github' => [
    'client_id' => env('GITHUB_CLIENT_ID'),
    'client_secret' => env('GITHUB_CLIENT_SECRET'),
    'redirect' => env('GITHUB_REDIRECT_URI'),
],
```

Die entsprechenden leeren Variablen in `.env.example` dokumentieren und nach lokalen Änderungen `php artisan config:clear` ausführen. Das Beispiel stellt keine Anbieterwahl dar.

## Anmeldeablauf

Zwei Web-Routen implementieren: Weiterleitung zum Anbieter mit `Socialite::driver('github')->redirect()` und Callback mit `Socialite::driver('github')->user()`. Für dieses Blade-Frontend die Session-basierte State-Prüfung beibehalten. Provider-Fehler und abgebrochene Anmeldungen abfangen.

Vor einer Benutzeranlage oder Kontoverknüpfung die Regeln festlegen: erlaubte Anbieter, Registrierung, verifizierte Identitäten und explizite Verknüpfung mit bestehenden Konten. Provider-ID und Anbieter gemeinsam eindeutig speichern; bestehende Konten nicht allein aufgrund einer übereinstimmenden E-Mail-Adresse automatisch verbinden. Privilegierte Spatie-Berechtigungen nicht automatisch an Social-Login-Benutzer vergeben. OAuth-Tokens nur speichern, wenn sie für die Anwendung benötigt werden.

Die späteren Integrationstests sollen erfolgreiche Anmeldung, ungültigen State, abgelehnten Provider-Zugriff und sichere Kontoverknüpfung prüfen. Ein echter Provider-Test erfordert die ausgewählte OAuth-Anwendung und deren Zugangsdaten.

Referenz: [Laravel Socialite](https://laravel.com/framework/docs/socialite).
