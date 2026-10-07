<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Process;
use PDO;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        config(['backup.kopia_binary' => 'kopia']);
        config(['backup.staging_directory' => storage_path('framework/testing/backup-'.bin2hex(random_bytes(8)))]);
    }

    protected function tearDown(): void
    {
        $directory = config('backup.staging_directory');
        if (is_file($directory.'/database.sqlite')) {
            unlink($directory.'/database.sqlite');
        }
        if (is_dir($directory)) {
            rmdir($directory);
        }
        parent::tearDown();
    }

    public function test_consistent_sqlite_copy_contains_application_data(): void
    {
        $user = User::factory()->create();
        $this->artisan('app:backup', ['--prepare-only' => true])->assertSuccessful();
        $copy = new PDO('sqlite:'.config('backup.staging_directory').'/database.sqlite');
        $this->assertSame('ok', $copy->query('PRAGMA integrity_check')->fetchColumn());
        $this->assertSame($user->email, $copy->query('SELECT email FROM users')->fetchColumn());
    }

    public function test_missing_repository_aborts_before_snapshot_creation(): void
    {
        Process::fake(['*' => Process::result(errorOutput: 'Repository not connected', exitCode: 1)]);
        $this->artisan('app:backup')->assertFailed();
        Process::assertRan(fn ($process) => $process->command === ['kopia', 'repository', 'status']);
        Process::assertNotRan(fn ($process) => in_array('snapshot', $process->command));
    }

    public function test_snapshot_failure_is_reported_as_failed_backup(): void
    {
        Process::fake([
            'kopia repository status' => Process::result(),
            '*' => Process::result(errorOutput: 'Snapshot failed', exitCode: 1),
        ]);
        $this->artisan('app:backup')->assertFailed();
        Process::assertRan(fn ($process) => $process->command === ['kopia', 'snapshot', 'create', '--fail-fast', base_path()]);
    }
}
