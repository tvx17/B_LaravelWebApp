<?php

namespace Tests\Feature;

use App\Console\Commands\Setup;
use Illuminate\Console\Application;
use Illuminate\Console\OutputStyle;
use Illuminate\Foundation\Console\ConfigClearCommand;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class SetupTest extends TestCase
{
    public function test_german_confirmation_answers_are_understood(): void
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, "ja\nnein\n");
        rewind($stream);
        $input = new ArrayInput([]);
        $input->setStream($stream);
        $command = new Setup;
        $command->setOutput(new OutputStyle($input, new BufferedOutput));
        $this->assertTrue($command->confirm('Installieren?'));
        $this->assertFalse($command->confirm('Installieren?'));
        fclose($stream);
    }

    public function test_noninteractive_setup_is_rejected(): void
    {
        $this->artisan('app:setup', ['--no-interaction' => true])->assertFailed();
    }

    public function test_env_changes_preserve_key_and_refresh_database_and_cors(): void
    {
        $originalBase = base_path();
        $originalConfig = config_path();
        $originalEnv = $_ENV;
        $originalServer = $_SERVER;
        $originalConnection = getenv('DB_CONNECTION');
        $originalDatabase = getenv('DB_DATABASE');
        $temporary = sys_get_temp_dir().'/laravel-setup-'.bin2hex(random_bytes(8));
        mkdir($temporary);
        file_put_contents($temporary.'/.env', "APP_KEY=base64:preserved\nAPP_ENV=local\nDB_CONNECTION=sqlite\nDB_DATABASE=old.sqlite\n");
        try {
            $this->app->setBasePath($temporary);
            $this->app->useConfigPath($originalConfig);
            $command = $this->app->make(Setup::class);
            $console = new Application($this->app, $this->app['events'], $this->app->version());
            $console->add($this->app->make(ConfigClearCommand::class));
            $console->add($command);
            $command->setLaravel($this->app);
            $command->setInput(new ArrayInput([]));
            $command->setOutput(new OutputStyle(new ArrayInput([]), new BufferedOutput));
            $method = new \ReflectionMethod(Setup::class, 'writeEnv');
            unset($_ENV['DB_CONNECTION'], $_ENV['DB_DATABASE'], $_SERVER['DB_CONNECTION'], $_SERVER['DB_DATABASE']);
            putenv('DB_CONNECTION');
            putenv('DB_DATABASE');
            $method->invoke($command, ['DB_CONNECTION' => 'mysql', 'DB_DATABASE' => 'setup_test', 'DB_PASSWORD' => 'a\'b$secret', 'CORS_ALLOWED_ORIGINS' => 'https://example.com', 'CORS_SUPPORTS_CREDENTIALS' => 'true']);
            $this->assertSame('mysql', config('database.default'));
            $this->assertSame('setup_test', config('database.connections.mysql.database'));
            $this->assertSame('a\'b$secret', config('database.connections.mysql.password'));
            $this->assertSame(['https://example.com'], config('cors.allowed_origins'));
            $this->assertTrue(config('cors.supports_credentials'));
            $this->assertStringContainsString('APP_KEY=base64:preserved', file_get_contents($temporary.'/.env'));
        } finally {
            $this->app->setBasePath($originalBase);
            $this->app->useConfigPath($originalConfig);
            $_ENV = $originalEnv;
            $_SERVER = $originalServer;
            putenv($originalConnection === false ? 'DB_CONNECTION' : 'DB_CONNECTION='.$originalConnection);
            putenv($originalDatabase === false ? 'DB_DATABASE' : 'DB_DATABASE='.$originalDatabase);
            File::deleteDirectory($temporary);
        }
    }
}
