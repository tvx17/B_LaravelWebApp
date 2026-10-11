# Laravel-Brain

`laramint/laravel-brain` 2.7.1 ist als Entwicklungsabhängigkeit installiert. Es analysiert die Anwendung und zeigt Beziehungen zwischen Routen, Klassen, Modellen und weiteren Komponenten als interaktiven Graphen. Der Provider wird automatisch erkannt; Routen und Befehle sind nur bei `APP_ENV=local` verfügbar.

## Verwendung

```powershell
php artisan brain:scan
php artisan serve
```

Anschließend `http://127.0.0.1:8000/_laravel-brain` öffnen. Nach Codeänderungen erneut scannen oder die Rescan-Funktion verwenden. Der erste Scan wurde unter Windows ausgeführt.

Graphdateien liegen in `storage/app/laravel-brain/` und sind von Git ausgeschlossen. `config/laravel-brain.php` verwendet den Dateitreiber und die Erkennung registrierter Routen; keine zusätzliche Datenbank oder Migration ist erforderlich. Übrige Analyseoptionen stammen aus den Paketdefaults. Das Standard-Speicherlimit beträgt 1024 MB; bei Bedarf `LARAVEL_BRAIN_MEMORY_LIMIT` lokal setzen.

## Betriebsgrenzen

Der lokale Viewer ist standardmäßig ohne Anmeldung erreichbar und kann Quellcode anzeigen. Den Entwicklungsserver für diese Verwendung auf `127.0.0.1` betreiben. Produktionsinstallationen mit `composer install --no-dev` enthalten das Paket nicht. Die statische Analyse ersetzt keine Laufzeitprüfung; dynamisch ermittelte Aufrufe können unvollständig sein.

`laramint/laravel-stress` wird als Paketabhängigkeit mitinstalliert. Es wurden keine Lasttests ausgeführt. Der optionale MCP-Server ist deaktiviert und kein zusätzliches MCP-Paket installiert.

Die Funktion zur Generierung von KI-Regeldateien wurde nicht ausgeführt. `AGENTS.md` muss gemäß Projektvorgabe erhalten bleiben; insbesondere keine Regelerzeugung verwenden, die diese Datei überschreibt.

Der erste vollständige Scan erfasste 77 Routen und meldete 31 heuristische Security-Hinweise. Diese sind keine bestätigten Schwachstellen und müssen im Kontext der lokalen Entwicklungswerkzeuge bewertet werden.

Referenz: [Paketdokumentation](https://github.com/laramint/laravel-brain).
