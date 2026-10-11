# Browser-Einrichtung

Der Browser-Assistent wurde auf die Struktur und den Ablauf aus `LaravelApi` umgestellt. Der Einstieg bleibt `/setup.php`; die Freischaltung erfolgt jetzt über eine private `.setup-access`-Datei im Projektstamm.

Die vollständige Anleitung mit Voraussetzungen, Sprachauswahl, Fortschrittsanzeige, Sicherheitsmechanismen und Hinweisen für den Wechsel vom bisherigen Installer steht in [docs/setup.md](setup.md).

Eine vorhandene `storage/app/web-setup.complete`-Sperre gilt weiterhin. `web-setup.local.php` aktiviert den neuen Assistenten nicht. Ohne ausdrückliche neue Freischaltung wird keine Einrichtung gestartet.
