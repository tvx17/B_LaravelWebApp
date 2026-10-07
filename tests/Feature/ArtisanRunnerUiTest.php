<?php

namespace Tests\Feature;

use App\Models\User;
use CodeSquirrel\ArtisanRunnerUI\ArtisanRunnerUIServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ArtisanRunnerUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['artisan-runner-ui.enabled' => true]);
        if (! $this->app['router']->has('artisan-runner-ui.index')) {
            $this->app->getProvider(ArtisanRunnerUIServiceProvider::class)->boot();
        }
    }

    public function test_ui_is_unavailable_outside_allowed_environments(): void
    {
        $this->getJson('/artisan-runner-ui')->assertNotFound();
    }

    public function test_guests_cannot_access_the_ui(): void
    {
        config(['artisan-runner-ui.environments' => ['testing']]);

        $this->getJson('/artisan-runner-ui')->assertUnauthorized();
        $this->get('/artisan-runner-ui')->assertRedirect('/login');
    }

    public function test_users_without_permission_cannot_run_commands(): void
    {
        config(['artisan-runner-ui.environments' => ['testing']]);

        $this->actingAs(User::factory()->create())
            ->postJson('/artisan-runner-ui/run', ['command' => 'permission:cache-reset'])
            ->assertForbidden();
    }

    public function test_authorized_users_can_view_ui_and_run_only_allowed_commands(): void
    {
        config(['artisan-runner-ui.environments' => ['testing']]);
        Permission::findOrCreate('run-artisan-runner-ui', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('run-artisan-runner-ui');

        $user->update(['password' => 'a-long-test-password']);
        $this->get('/artisan-runner-ui')->assertRedirect('/login');
        $this->post('/login', ['email' => $user->email, 'password' => 'a-long-test-password'])
            ->assertRedirect('/artisan-runner-ui');
        $this->get('/artisan-runner-ui')->assertOk();
        $this->postJson('/artisan-runner-ui/run', ['command' => 'permission:cache-reset'])
            ->assertOk()->assertJson(['exit_code' => 0]);
        $this->postJson('/artisan-runner-ui/run', ['command' => 'migrate:fresh'])
            ->assertForbidden();
    }
}
