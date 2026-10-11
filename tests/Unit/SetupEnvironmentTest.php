<?php

namespace Tests\Unit;

use App\Support\SetupEnvironment;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

class SetupEnvironmentTest extends TestCase
{
    public function test_values_round_trip_without_interpolation(): void
    {
        foreach (['', 'true', 'false', 'null', 'a # b', '${APP_NAME}', 'C:\\data\\database.sqlite', 'a\'b"c$secret\\path'] as $value) {
            $this->assertSame($value, Dotenv::parse('VALUE='.SetupEnvironment::quote($value))['VALUE']);
        }
    }

    public function test_multiline_values_are_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        SetupEnvironment::quote("value\nAPP_DEBUG=true");
    }
}
