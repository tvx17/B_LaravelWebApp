<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationsGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_sqlite_schema_can_be_generated_and_recreated_without_logging_migrations(): void
    {
        Schema::create('generator_fixtures', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->text('body')->nullable();
        });
        $migrationCount = DB::table('migrations')->count();
        $directory = storage_path('framework/testing/generator-'.bin2hex(random_bytes(8)));
        mkdir($directory, 0700, true);
        try {
            $this->artisan('migrate:generate', [
                '--tables' => 'generator_fixtures', '--path' => $directory,
                '--skip-log' => true, '--no-interaction' => true,
            ])->assertSuccessful();
            $files = glob($directory.'/*.php');
            $this->assertCount(1, $files);
            $this->assertSame($migrationCount, DB::table('migrations')->count());
            $migration = require $files[0];
            // Recreate only this test table on the isolated in-memory database.
            Schema::drop('generator_fixtures');
            $migration->up();
            $this->assertTrue(Schema::hasColumns('generator_fixtures', ['id', 'slug', 'body']));
            $this->assertTrue(Schema::hasIndex('generator_fixtures', ['slug'], 'unique'));
            DB::table('generator_fixtures')->insert(['slug' => 'example', 'body' => null]);
            $this->assertDatabaseHas('generator_fixtures', ['slug' => 'example', 'body' => null]);
        } finally {
            foreach (glob($directory.'/*') as $file) {
                unlink($file);
            }
            rmdir($directory);
        }
    }
}
