<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FilamentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_panel_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk()->assertSee('email');
    }

    public function test_users_without_permission_cannot_access_the_panel(): void
    {
        $user = User::factory()->create();
        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_authorized_users_can_access_the_panel_with_the_existing_session(): void
    {
        $user = User::factory()->create(['password' => 'a-long-test-password']);
        $user->givePermissionTo(Permission::findOrCreate('access-admin', 'web'));
        $this->post('/login', ['email' => $user->email, 'password' => 'a-long-test-password'])
            ->assertRedirect('/dashboard');
        $this->get('/admin')->assertOk()->assertSee($user->name);
        $this->get('/dashboard')->assertOk()->assertSee('Admin-Panel öffnen');
    }

    public function test_create_user_can_grant_admin_access_without_runner_access(): void
    {
        $this->artisan('app:create-user', ['email' => 'admin@example.com', '--name' => 'Admin', '--admin' => true])
            ->expectsQuestion('Passwort (mindestens 12 Zeichen)', 'a-long-test-password')
            ->expectsQuestion('Passwort wiederholen', 'a-long-test-password')
            ->expectsOutput('Benutzer angelegt.')
            ->assertSuccessful();
        $user = User::where('email', 'admin@example.com')->firstOrFail();
        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));
        $this->assertFalse($user->can('run-artisan-runner-ui'));
    }

    public function test_filament_login_accepts_authorized_users(): void
    {
        $user = User::factory()->create(['password' => 'a-long-test-password']);
        $user->givePermissionTo(Permission::findOrCreate('access-admin', 'web'));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(Login::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'a-long-test-password')
            ->call('authenticate')
            ->assertHasNoErrors()
            ->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    public function test_filament_login_rejects_users_without_panel_permission(): void
    {
        $user = User::factory()->create(['password' => 'a-long-test-password']);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Livewire::test(Login::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'a-long-test-password')
            ->call('authenticate')
            ->assertHasErrors(['data.email']);
        $this->assertGuest();
    }
}
