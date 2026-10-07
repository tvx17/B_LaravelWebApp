# Spatie Media Library

Das Basispaket ist installiert; die Konfiguration liegt in `config/media-library.php`, die zugehörige Migration erstellt die Tabelle `media`. Laravel registriert das Paket automatisch.

## Speicher und Modelle

`MEDIA_DISK=local` verwendet zunächst Laravels privaten Speicher unter `storage/app/private`. Diese Dateien sind nicht über `/storage` öffentlich abrufbar. Für geschützte Downloads später eine autorisierte Route mit Besitzer-/Berechtigungsprüfung einrichten. Für ausdrücklich öffentliche Dateien lässt sich eine Collection auf `public` speichern; dafür `php artisan storage:link` ausführen. Die Zuordnung zu fachlichen Modellen wird später festgelegt.

Ein Modell, das Dateien verwalten soll, muss `HasMedia` implementieren und `InteractsWithMedia` verwenden:

```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Document extends Model implements HasMedia
{
    use InteractsWithMedia;
}
```

`Document` ist ein Beispiel, kein bereits vorhandenes Modell. Nach Validierung und Autorisierung kann eine Datei mit `$document->addMediaFromRequest('file')->toMediaCollection('attachments')` zugeordnet werden. MIME-Typen, Dateigröße, zulässige Collections und Benutzerrechte müssen bei der späteren Upload-Funktion geprüft werden. Die Basisinstallation erzeugt keine Upload-Routen oder Benutzeroberfläche.

## Bildverarbeitung und Queues

Die Windows-PHP-Umgebung verfügt über EXIF, GD und Imagick. Für normale Bildkonvertierungen wird GD konfiguriert. Konvertierungen verwenden standardmäßig die bestehende Laravel-Queue; für asynchrone Verarbeitung `php artisan queue:work` starten. Es sind noch keine Konvertierungen definiert.

Ghostscript für PDF-/SVG-Vorschaubilder, FFmpeg für Video-Thumbnails und optionale Optimierungsprogramme sind nicht eingerichtet. Solche Funktionen erst nach Bereitstellung und Prüfung der jeweiligen Windows-Werkzeuge aktivieren.

## Betrieb und Sicherung

- `php artisan migrate`: Medientabelle auf weiteren Installationen anlegen.
- `php artisan config:clear`: nach Änderungen am Medienspeicher ausführen.
- `php artisan test`: Integration einschließlich Dateizuordnung und Löschung prüfen.

Die Kopia-Vorbereitung nimmt `storage/app` und die konsistente SQLite-Kopie auf: Medien und deren Datenbankzuordnungen sind damit Bestandteil des vorgesehenen Backups. Ein echtes Backup-Repository ist weiterhin noch nicht verbunden. Bei einem späteren Wechsel zu S3 muss dessen Sicherung gesondert festgelegt werden.

Referenz: [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary/v11/introduction).
