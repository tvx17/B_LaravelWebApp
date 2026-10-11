<?php

namespace App\Support;

use Dotenv\Dotenv;
use InvalidArgumentException;

class SetupEnvironment
{
    public static function quote(string $value): string
    {
        if (str_contains($value, "\n") || str_contains($value, "\r") || str_contains($value, "\0")) {
            throw new InvalidArgumentException('Mehrzeilige Werte sind nicht erlaubt.');
        }

        $quoted = ! str_contains($value, "'")
            ? "'".$value."'"
            : '"'.str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value).'"';

        if (Dotenv::parse('VALUE='.$quoted)['VALUE'] !== $value) {
            throw new InvalidArgumentException('Wert kann nicht verlustfrei gespeichert werden.');
        }

        return $quoted;
    }
}
