<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\BrowserSetup;
use Dotenv\Dotenv;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class BrowserSetupTest extends TestCase
{
    private string $fixture;

    private BrowserSetup $setup;

    protected function setUp(): void
    {
        $source = dirname(__DIR__, 2);
        $this->fixture = sys_get_temp_dir().'/browser-setup-'.bin2hex(random_bytes(8));
        foreach (['vendor', 'config', 'bootstrap/cache', 'database/migrations', 'routes', 'app', 'public', 'storage/app', 'storage/framework/views', 'storage/framework/sessions', 'storage/logs'] as $directory) {
            mkdir($this->fixture.'/'.$directory, 0700, true);
        }
        foreach (glob($source.'/config/*.php') as $path) {
            copy($path, $this->fixture.'/config/'.basename($path));
        }
        foreach (glob($source.'/database/migrations/*.php') as $path) {
            copy($path, $this->fixture.'/database/migrations/'.basename($path));
        }
        copy($source.'/.env.example', $this->fixture.'/.env.example');
        file_put_contents($this->fixture.'/vendor/autoload.php', '<?php require '.var_export($source.'/vendor/autoload.php', true).';');
        file_put_contents($this->fixture.'/bootstrap/app.php', <<<'PHP'
<?php
return Illuminate\Foundation\Application::configure(basePath: dirname(__DIR__))
    ->withProviders([Spatie\Permission\PermissionServiceProvider::class])
    ->withRouting(web: __DIR__.'/../routes/web.php', health: '/up')->create();
PHP);
        file_put_contents($this->fixture.'/routes/web.php', '<?php Illuminate\Support\Facades\Route::get("/", fn () => "fixture");');
        // This process owns only an isolated fixture, never the repository .env/database.
        foreach (['APP_KEY' => 'base64:'.base64_encode(str_repeat('a', 32)), 'APP_URL' => 'https://fixture.invalid', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $this->fixture.'/database/database.sqlite'] as $name => $value) {
            $_ENV[$name] = $_SERVER[$name] = $value;
            putenv($name.'='.$value);
        }
        $this->setup = new BrowserSetup($this->fixture, ['allow_processes' => false]);
        $this->setup->run('env', []);
    }

    protected function tearDown(): void
    {
        // Release SQLite handles before cleanup on Windows.
        if (Container::getInstance()->bound('db')) {
            Container::getInstance()->make('db')->disconnect();
        }
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->fixture, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($this->fixture);
    }

    public function test_existing_env_key_and_cors_are_preserved_and_updated_safely(): void
    {
        $this->setup->run('key', []);
        $env = Dotenv::parse(file_get_contents($this->fixture.'/.env'));
        $this->assertSame(32, strlen(base64_decode(substr($env['APP_KEY'], 7), true)));
        $before = file_get_contents($this->fixture.'/.env');
        $this->setup->run('key', []);
        $this->setup->run('env', []);
        $this->assertSame($before, file_get_contents($this->fixture.'/.env'));
        $this->setup->run('cors', ['origins' => 'https://frontend.example.com, https://other.example.com:8443', 'credentials' => '1']);
        $env = Dotenv::parse(file_get_contents($this->fixture.'/.env'));
        $this->assertSame('https://frontend.example.com,https://other.example.com:8443', $env['CORS_ALLOWED_ORIGINS']);
        $this->assertSame('true', $env['CORS_SUPPORTS_CREDENTIALS']);
    }

    public function test_migrations_and_user_with_both_permissions_without_process_execution(): void
    {
        $this->setup->run('key', []);
        $this->setup->run('database', ['driver' => 'sqlite', 'create_database' => '1']);
        $this->setup->run('migrate', []);
        $this->setup->run('user', ['name' => 'Setup Admin', 'email' => 'setup@example.com', 'password' => 'StrongPassword123!', 'password_confirmation' => 'StrongPassword123!']);
        $user = User::where('email', 'setup@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('StrongPassword123!', $user->password));
        $this->assertTrue($user->can('access-admin'));
        $this->assertTrue($user->can('run-artisan-runner-ui'));
        $this->setup->run('migrate', []);
        $this->assertSame(1, User::count());
        $report = $this->setup->run('finish', ['save_report' => '1', 'history' => ['npm' => 'Übersprungen']]);
        $this->assertFileExists($this->fixture.'/storage/app/web-setup.complete');
        $this->assertSame($report, file_get_contents($this->fixture.'/SETUP.md'));
        $this->assertStringContainsString('https://fixture.invalid/', $report);
        $this->assertStringContainsString('Frontend-Abhängigkeiten: Übersprungen', $report);
        $this->assertStringNotContainsString('StrongPassword', $report);
        $this->assertStringNotContainsString('/admin |', $report); // Fixture has no Filament routes.
    }

    public function test_invalid_cors_cannot_modify_configuration(): void
    {
        $before = file_get_contents($this->fixture.'/.env');
        try {
            $this->setup->run('cors', ['origins' => 'https://example.com/private']);
            $this->fail('Invalid origin accepted.');
        } catch (RuntimeException) {
            $this->assertSame($before, file_get_contents($this->fixture.'/.env'));
        }
    }

    public function test_external_processes_require_explicit_configuration(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Prozessausführung nicht freigegeben');
        $this->setup->run('composer', []);
    }
}
