# Eloquent-Sluggable

`cviebrock/eloquent-sluggable` 13.0.1 ist für Laravel 13 installiert. Der Service-Provider wird automatisch erkannt; globale Einstellungen stehen in `config/sluggable.php`. Das Paket benötigt keine Linux-spezifischen Werkzeuge.

## Verwendung in Inhaltsmodellen

Für ein späteres Inhaltsmodell das Trait und die Quellspalte ergänzen:

```php
use Cviebrock\EloquentSluggable\Sluggable;

class Post extends Model
{
    use Sluggable;

    public function sluggable(): array
    {
        return ['slug' => ['source' => 'title']];
    }
}
```

In der zugehörigen Migration eine Spalte mit eindeutigem Index anlegen:

```php
$table->string('slug')->unique();
```

`title` muss beim Anlegen gesetzt und bei `create()` für Mass Assignment freigegeben sein. Das Paket erzeugt beim Speichern beispielsweise `hallo-laravel`; weitere gleiche Titel erhalten `hallo-laravel-2`. `unique=true` prüft vorhandene Slugs, der Datenbankindex schützt zusätzlich vor gleichzeitigen Schreibzugriffen. Konflikte bei paralleler Erstellung gegebenenfalls mit erneutem Erzeugen/Speichern behandeln.

## Stabile URLs und Weiterentwicklung

`onUpdate=false` erhält bestehende Slugs bei Titeländerungen. Quellfeld und gegebenenfalls maximale Länge pro Modell festlegen; dabei Platz für Eindeutigkeitssuffixe in der Datenbankspalte berücksichtigen. Für vorhandene Datensätze einen kontrollierten Backfill vor dem eindeutigen Pflichtfeld planen.

Optional Slug-Routen mit `{post:slug}` verwenden oder `getRouteKeyName()` im Modell auf `slug` setzen. Reservierte Begriffe und Soft-Delete-Verhalten abhängig von den tatsächlichen Routen festlegen. Das vorhandene User-Modell erhält keinen Slug; fachliche Inhaltsmodelle sind noch nicht definiert.

Prüfung: `php artisan test --filter=SluggableTest`.

Referenz: [Paketdokumentation](https://github.com/cviebrock/eloquent-sluggable).
