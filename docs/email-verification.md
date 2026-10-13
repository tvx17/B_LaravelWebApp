# Native E-Mail-Verifikation

Stand: 2026-10-13. Native Laravel-/Fortify-Verifikation ist implementiert. Ein zusätzliches Verifikationspaket wird nicht benötigt. Das zuvor geprüfte [jrean-Paket](https://github.com/jrean/laravel-user-verification) unterstützt laut README Laravel 13, würde hier aber einen separaten Zustand mit `verified` und `verification_token` einführen.

## Zustand und Zugang

`User` implementiert `MustVerifyEmail`; Verifikationsstatus ist das bestehende `email_verified_at`. Fortify aktiviert `Features::emailVerification()` und rendert `resources/views/auth/verify-email.blade.php`. Keine zusätzliche Migration oder Composer-Abhängigkeit ist erforderlich.

Dashboard und Runner verlangen `auth` und `verified`; Runner benötigt zusätzlich seine Spatie-Berechtigung und bleibt lokal. Filament verwendet seine native Verifikationsseite und verlangt weiterhin `access-admin`. Ein bestätigtes Konto erhält durch die Verifikation keine zusätzlichen Rechte.

Neue Benutzer aus `app:create-user`, `setup:admin` und Browser-Setup lösen nach erfolgreicher Datenbanktransaktion das `Registered`-Event aus. Dadurch wird die native Bestätigungsmail versendet. Vorhandene Benutzer werden weder automatisch bestätigt noch durch erneute Rechtevergabe benachrichtigt. Sie können sich anmelden und einen Link anfordern; bisher unverifizierte Konten müssen dies vor Nutzung der geschützten Bereiche tun.

## Browser-Ablauf

| Aufruf | Verhalten |
|---|---|
| `GET /email/verify` | Hinweis und Formular zum erneuten Versand; Anmeldung erforderlich |
| `POST /email/verification-notification` | Fortify-Versand; Anmeldung, CSRF und Begrenzung auf sechs Anfragen pro Minute |
| `GET /email/verify/{id}/{hash}` | Native Bestätigung; Anmeldung, gültige Signatur und passendes Konto erforderlich |

Die native Benachrichtigung enthält standardmäßig einen signierten Link mit 60 Minuten Gültigkeit. Nach Bestätigung führt Fortify zur ursprünglich angeforderten Seite oder `/dashboard?verified=1`. Filament besitzt zusätzlich seine eigenen Verifikationsrouten; beide Abläufe verwenden denselben Status im User-Modell.

## JSON-API mit Actions

Die Actions liegen in `app/Actions/Auth/`. Diese Endpunkte sind **Session-APIs** mit Laravel-`web`-Middleware, keine Bearer-Token-API. Sie verwenden dieselbe `web`-Session wie das Blade-Frontend. Für POST sind Session-Cookie und CSRF-Token nötig. Keine Sanctum- oder API-Login-Installation wurde ergänzt.

| Methode / Pfad | Antwort bei Erfolg |
|---|---|
| `GET /api/email-verification/status` | `{"verified":false}` beziehungsweise `true` |
| `POST /api/email-verification/notification` | `{"status":"verification-link-sent"}`; bereits bestätigt: `{"status":"already-verified"}` ohne weitere Mail |
| `GET /api/email-verification/verify/{id}/{hash}` | `{"verified":true}` bei authentifiziertem, passendem Konto und gültiger URL-Signatur |

Für JSON-Aufrufe `Accept: application/json` setzen. Fehlende Anmeldung: 401, ungültige/fremde/abgelaufene Signatur: 403, ungültiger CSRF-Token: 419, zu viele Versand-/Bestätigungsanfragen: 429. Der erneute Versand geht ausschließlich an das angemeldete Konto; eine übergebene fremde E-Mail-Adresse wird nicht verwendet. Signierte API-URLs müssen über Laravels `URL::temporarySignedRoute('api.verification.verify', ...)` erzeugt werden. Die normalen Mails verwenden den Browser-Link; dieser kann mit JSON-Accept ebenfalls über Fortify verarbeitet werden.

Beispiel aus einer Seite derselben Anwendung (CSRF-Token aus `csrf_token()`):

```javascript
await fetch('/api/email-verification/notification', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
    },
});
```

## Mail und Installationsassistent

Der Assistent zeigt vor Administratoranlage den Verifikations-/Mailhinweis, im Browser in allen fünf Sprachen. Seine Abschlussübersicht enthält `/email/verify`. Weder bestehende Administratoren noch neu angelegte Konten werden durch Setup automatisch bestätigt.

`MAIL_MAILER=log` schreibt Nachrichten nur ins lokale Log und stellt sie nicht zu. Für echte Mails `MAIL_MAILER`, Host, Port, Schema, Zugangsdaten und Absender gemäß `config/mail.php` in der privaten `.env` konfigurieren. `APP_URL` muss der tatsächlichen öffentlichen Adresse entsprechen; danach `php artisan config:clear`. SMTP-Zugangsdaten und signierte Links nicht öffentlich dokumentieren.

Die Benachrichtigung wird derzeit synchron nach dem Commit versendet; ein Worker ist dafür nicht erforderlich. Bei Mailfehlern kann das Konto bereits existieren. Mailkonfiguration korrigieren, anmelden und erneut anfordern. Für vorhandene Administratoren vor Umstellung den Empfangsweg sicherstellen. Es werden keine alten Datensätze pauschal als bestätigt markiert.

Tests verwenden ausschließlich isolierte Datenbank und simulierte Benachrichtigungen beziehungsweise den Test-Mailer: `php artisan test --filter=EmailVerificationTest` und vollständige Suite. Ein tatsächlicher SMTP-Zustellungstest bleibt bis zur Mailkonfiguration offen.

Referenz: [Laravel 13 E-Mail-Verifikation](https://laravel.com/framework/docs/13.x/verification).
