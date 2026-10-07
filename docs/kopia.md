# Backups mit Kopia unter Windows

Kopia CLI 0.23.1 ist für den aktuellen Windows-Benutzer installiert. Kopia ist ein eigenständiges Programm; Laravel ruft es über `php artisan app:backup` auf. Es wird kein Backup-Web-Endpunkt bereitgestellt.

## Repository einmalig verbinden

Der endgültige Backup-Speicherort ist noch festzulegen. Er muss außerhalb des Projektverzeichnisses liegen, idealerweise auf einem separaten Laufwerk, NAS oder Cloud-Speicher. Ein Repository im Projekt würde sich selbst sichern.

Nach einem Neustart des Terminals ist `kopia` im PATH. Für ein neues lokales Repository:

```powershell
kopia repository create filesystem --path "E:\Backups\LaravelWebApp"
```

Der Pfad ist ein Beispiel, kein bereits eingerichtetes Ziel. Das Repository-Passwort lokal in Kopia eingeben und sicher verwahren. Für ein bestehendes Repository stattdessen `kopia repository connect filesystem --path "E:\Backups\LaravelWebApp"` verwenden. Passwörter weder in Git noch in dieser Dokumentation oder Kommandozeilenargumenten speichern. Kopia verwendet unter Windows seine benutzerbezogene Konfiguration und Credential-Verwaltung; geplante Aufgaben müssen unter demselben Benutzer mit Zugriff auf diese Konfiguration laufen.

## Sicherung

```powershell
php artisan app:backup --prepare-only
php artisan app:backup
kopia snapshot list
```

`--prepare-only` erzeugt ausschließlich `storage/app/backup-staging/database.sqlite`: eine konsistente SQLite-Kopie mittels `VACUUM INTO`, anschließend mit `PRAGMA integrity_check` geprüft. Die normale Sicherung prüft zuerst die Kopia-Verbindung, erstellt die Kopie und sichert dann das Projekt. Fehler führen zu einem Fehler-Exitcode; eine Sperre verhindert parallele Läufe dieses Befehls.

`.kopiaignore` schließt Abhängigkeiten, Git-Metadaten, Caches, Logs, Debugbar-Profile und rohe SQLite-Dateien unter `database/` aus. Quellcode, Lockfiles, `.env`, Uploads unter `storage/app/` und die vorbereitete Datenbankkopie werden aufgenommen. Umgebungsdateien sind für die Wiederherstellung wichtig und liegen im verschlüsselten Repository. Git-Ignorierregeln gelten nicht automatisch für Kopia.

Die Staging-Kopie enthält sensible Anwendungsdaten und bleibt lokal bis zum nächsten Lauf liegen. Zugriff auf das Projektverzeichnis entsprechend begrenzen. Datei-Uploads und Datenbank werden nicht als ein gemeinsamer atomarer Snapshot erstellt; für Wiederherstellungspunkte mit strikter Konsistenz Anwendungsschreibzugriffe und Worker während der Sicherung pausieren.

Für Kopia außerhalb des PATH `KOPIA_BINARY="C:/Pfad/kopia.exe"` in `.env` setzen und `php artisan config:clear` ausführen. Aktuell wird SQLite unterstützt. Bei MariaDB bricht der Befehl bewusst ab, bis ein konsistenter SQL-Dump mit `mariadb-dump`/`mysqldump` in den Ablauf integriert wurde.

## Aufbewahrung und Zeitplanung

Nach Verbindung des endgültigen Repositorys beispielsweise:

```powershell
kopia policy set "C:\Entwicklung\Projekte\Boilerplates\LaravelWebApp" --keep-daily=7 --keep-weekly=4 --keep-monthly=12
```

Die Aufbewahrung muss zu Speicherplatz und Wiederherstellungsanforderungen passen. Für regelmäßige Sicherungen eine Windows-Aufgabe mit `php.exe` als Programm, `artisan app:backup` als Argumenten und dem Projektverzeichnis als Arbeitsverzeichnis einrichten. Keine unabhängigen Kopia-Zeitpläne für diesen Quellpfad aktivieren: Sie würden die vorherige Datenbankvorbereitung überspringen. Zeitplan, Repository und Aufbewahrung sind noch nicht eingerichtet.

## Wiederherstellung prüfen

```powershell
kopia snapshot list
kopia snapshot restore SNAPSHOT_ID "E:\Restore-Test\LaravelWebApp"
```

Immer zunächst in ein separates leeres Verzeichnis wiederherstellen. Den Platzhalter durch eine tatsächliche Snapshot-ID ersetzen. Mit PHP/PDO `PRAGMA integrity_check` auf der wiederhergestellten Staging-Datenbank ausführen und erwartete Daten prüfen. Für die Wiederherstellung der Anwendung die geprüfte Kopie bei gestoppter Anwendung als `database/database.sqlite` übernehmen, Abhängigkeiten aus den Lockfiles installieren und `php artisan config:clear` ausführen. Erst nach einem erfolgreichen Restore-Test ist das Backup betrieblich verifiziert.

Referenzen: [Windows-Installation](https://kopia.io/docs/installation/), [Repository-Einrichtung](https://kopia.io/docs/getting-started/), [Ausschlüsse](https://kopia.io/docs/advanced/kopiaignore/).
