<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class BrowserSetupHttpTest extends TestCase
{
    public function test_standalone_login_csrf_steps_and_completion_lock(): void
    {
        $source = dirname(__DIR__, 2);
        $fixture = sys_get_temp_dir().'/browser-http-'.bin2hex(random_bytes(8));
        foreach (['public', 'app/Support', 'storage/app', 'storage/framework', 'sessions'] as $directory) {
            mkdir($fixture.'/'.$directory, 0700, true);
        }
        copy($source.'/public/setup.php', $fixture.'/public/setup.php');
        copy($source.'/app/Support/BrowserSetup.php', $fixture.'/app/Support/BrowserSetup.php');
        copy($source.'/.env.example', $fixture.'/.env.example');
        $token = bin2hex(random_bytes(32));
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        $address = stream_socket_get_name($socket, false);
        fclose($socket);
        $log = tmpfile();
        $process = proc_open([PHP_BINARY, '-d', 'session.save_path='.$fixture.'/sessions', '-S', $address, '-t', $fixture.'/public'], [0 => ['pipe', 'r'], 1 => $log, 2 => $log], $pipes, $fixture);
        $this->assertIsResource($process);
        fclose($pipes[0]);
        $cookie = '';
        $request = function (array $post = []) use ($address, &$cookie): array {
            $headers = 'Cookie: '.$cookie."\r\n";
            if ($post !== []) {
                $headers .= "Content-Type: application/x-www-form-urlencoded\r\n";
            }
            $context = stream_context_create(['http' => [
                'method' => $post === [] ? 'GET' : 'POST',
                'header' => $headers,
                'content' => $post === [] ? '' : http_build_query($post),
                'ignore_errors' => true,
                'follow_location' => 0,
                'timeout' => 5,
            ]]);
            $body = @file_get_contents('http://'.$address.'/setup.php', false, $context);
            $responseHeaders = http_get_last_response_headers() ?? [];
            foreach ($responseHeaders as $header) {
                if (preg_match('/^Set-Cookie: (laravel_browser_setup=[^;]+)/i', $header, $match)) {
                    $cookie = $match[1];
                }
            }
            preg_match('/\s(\d{3})\s/', $responseHeaders[0] ?? '', $status);

            return [(int) ($status[1] ?? 0), (string) $body, $responseHeaders];
        };
        $csrf = function (string $body): string {
            $this->assertSame(1, preg_match('/name="csrf" value="([a-f0-9]+)"/', $body, $match));

            return $match[1];
        };
        try {
            for ($attempt = 0; $attempt < 30; $attempt++) {
                [$status] = $request();
                if ($status !== 0) {
                    break;
                }
                usleep(100000);
            }
            $this->assertSame(404, $status); // Disabled by default, before vendor exists.
            file_put_contents($fixture.'/web-setup.local.php', '<?php return '.var_export(['access_token' => $token, 'allow_processes' => false], true).';');
            [$status, $body, $headers] = $request();
            $this->assertSame(200, $status);
            $this->assertStringContainsString('Geschützte Einrichtung', $body);
            $this->assertStringContainsString("frame-ancestors 'none'", implode("\n", $headers));
            $loginCsrf = $csrf($body);
            $this->assertSame(403, $request(['action' => 'run', 'csrf' => 'bad'])[0]);
            [$status, $body] = $request(['action' => 'run', 'csrf' => $loginCsrf]);
            $this->assertStringContainsString('Zugangscode ungültig', $body);
            $this->assertFileDoesNotExist($fixture.'/.env');
            $this->assertSame(303, $request(['csrf' => $loginCsrf, 'access_token' => $token])[0]);
            [$status, $body] = $request();
            $this->assertStringContainsString('Schritt 1 von 12', $body);
            $stepCsrf = $csrf($body);
            $this->assertNotSame($loginCsrf, $stepCsrf);
            $this->assertSame(403, $request(['csrf' => $loginCsrf, 'action' => 'run'])[0]);
            $this->assertSame(303, $request(['csrf' => $stepCsrf, 'action' => 'run'])[0]);
            $this->assertFileExists($fixture.'/.env');
            [$status, $body] = $request();
            $this->assertStringContainsString('Schritt 2 von 12', $body);
            $this->assertStringContainsString('Dein Hosting erlaubt hier keine', $body);
            $this->assertSame(403, $request(['csrf' => $stepCsrf, 'action' => 'run'])[0]); // No double-submit into the next action.
            // Skip remaining actions, then finish even without vendor/.
            for ($step = 1; $step < 11; $step++) {
                [, $body] = $request();
                $stepCsrf = $csrf($body);
                $this->assertSame(303, $request(['csrf' => $stepCsrf, 'action' => 'skip'])[0]);
            }
            [, $body] = $request();
            $stepCsrf = $csrf($body);
            [$status, $body] = $request(['csrf' => $stepCsrf, 'action' => 'run', 'save_report' => '1']);
            $this->assertSame(200, $status);
            $this->assertStringContainsString('Einrichtung abgeschlossen', $body);
            $this->assertStringContainsString('noch nicht lauffähig', $body);
            $this->assertFileExists($fixture.'/SETUP.md');
            $this->assertFileExists($fixture.'/storage/app/web-setup.complete');
            $this->assertSame(404, $request()[0]);
        } finally {
            proc_terminate($process);
            proc_close($process);
            fclose($log);
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($fixture, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $item) {
                $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
            }
            rmdir($fixture);
        }
    }
}
