<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use PDO;
use RuntimeException;
use Throwable;

class Backup extends Command
{
    protected $signature = 'app:backup {--prepare-only : Nur eine konsistente SQLite-Kopie erstellen}';

    protected $description = 'SQLite vorbereiten und das Projekt mit Kopia sichern';

    public function handle(): int
    {
        $lock = Cache::lock('app-kopia-backup', config('backup.timeout') + 60);
        if (! $lock->get()) {
            $this->error('Eine Sicherung läuft bereits.');

            return self::FAILURE;
        }

        $temporary = null;
        try {
            $connection = DB::connection();
            if ($connection->getDriverName() !== 'sqlite') {
                throw new RuntimeException('Aktuell wird SQLite unterstützt. Vor dem Wechsel auf MariaDB einen konsistenten SQL-Dump in diesen Ablauf integrieren.');
            }

            if (! $this->option('prepare-only')) {
                Process::path(base_path())->timeout(30)
                    ->run([config('backup.kopia_binary'), 'repository', 'status'])->throw();
            }

            $directory = config('backup.staging_directory');
            if (! is_dir($directory) && ! mkdir($directory, 0700, true) && ! is_dir($directory)) {
                throw new RuntimeException('Backup-Verzeichnis konnte nicht angelegt werden.');
            }
            $temporary = $directory.'/'.bin2hex(random_bytes(8)).'.tmp';
            $connection->statement('VACUUM INTO '.$connection->getPdo()->quote($temporary));
            $copy = new PDO('sqlite:'.$temporary);
            if ($copy->query('PRAGMA integrity_check')->fetchColumn() !== 'ok') {
                throw new RuntimeException('Integritätsprüfung der SQLite-Kopie fehlgeschlagen.');
            }
            $copy = null;
            $destination = $directory.'/database.sqlite';
            // Only the previous staging copy is replaced; the live database is untouched.
            if (is_file($destination) && ! unlink($destination)) {
                throw new RuntimeException('Vorherige Backup-Kopie konnte nicht ersetzt werden.');
            }
            if (! rename($temporary, $destination)) {
                throw new RuntimeException('Backup-Kopie konnte nicht bereitgestellt werden.');
            }
            $temporary = null;
            $this->info('Konsistente SQLite-Kopie erstellt und geprüft.');

            if (! $this->option('prepare-only')) {
                $result = Process::path(base_path())->timeout(config('backup.timeout'))
                    ->run([config('backup.kopia_binary'), 'snapshot', 'create', '--fail-fast', base_path()]);
                $result->throw();
                $this->line($result->output());
                $this->info('Kopia-Snapshot erstellt.');
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            if ($temporary !== null && is_file($temporary)) {
                unlink($temporary);
            }
            $lock->release();
        }
    }
}
