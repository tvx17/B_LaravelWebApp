<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_form(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Anmelden');
    }

    public function test_users_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create(['password' => 'a-long-test-password']);
        $this->post('/login', ['email' => $user->email, 'password' => 'a-long-test-password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertOk()->assertSee($user->name);
        $this->withSession(['private-data' => 'value'])->post('/logout')
            ->assertRedirect('/login')->assertSessionMissing('private-data');
        $this->assertGuest();
    }

    public function test_invalid_password_is_rejected_and_never_flashed(): void
    {
        $user = User::factory()->create();
        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'incorrect'])
            ->assertRedirect('/login')->assertSessionHasErrors('email')
            ->assertSessionMissing('_old_input.password');
        $this->assertGuest();
    }

    public function test_login_attempts_are_throttled(): void
    {
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post('/login', ['email' => 'unknown@example.com', 'password' => 'incorrect']);
        }
        $this->post('/login', ['email' => 'unknown@example.com', 'password' => 'incorrect'])
            ->assertStatus(429);
    }

    public function test_create_user_command_hashes_password_and_grants_runner_access(): void
    {
        $this->artisan('app:create-user', ['email' => 'runner@example.com', '--name' => 'Runner', '--runner' => true])
            ->expectsQuestion('Passwort (mindestens 12 Zeichen)', 'a-long-test-password')
            ->expectsQuestion('Passwort wiederholen', 'a-long-test-password')
            ->expectsOutput('Benutzer angelegt.')
            ->assertSuccessful();
        $user = User::where('email', 'runner@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('a-long-test-password', $user->password));
        $this->assertTrue($user->can('run-artisan-runner-ui'));
    }

    public function test_create_user_rejects_unconfirmed_password(): void
    {
        $this->artisan('app:create-user', ['email' => 'runner@example.com', '--name' => 'Runner'])
            ->expectsQuestion('Passwort (mindestens 12 Zeichen)', 'a-long-test-password')
            ->expectsQuestion('Passwort wiederholen', 'different-password')
            ->assertFailed();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_password_confirmation_requires_the_correct_password(): void
    {
        $user = User::factory()->create(['password' => 'a-long-test-password']);
        $this->actingAs($user)->get('/user/confirm-password')->assertOk()->assertSee('Passwort bestätigen');
        $this->post('/user/confirm-password', ['password' => 'incorrect'])
            ->assertSessionHasErrors('password')->assertSessionMissing('auth.password_confirmed_at');
        $this->post('/user/confirm-password', ['password' => 'a-long-test-password'])
            ->assertRedirect('/dashboard')->assertSessionHas('auth.password_confirmed_at');
    }

    public function test_optional_account_features_are_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
        $this->get('/forgot-password')->assertNotFound();
        $this->get('/two-factor-challenge')->assertNotFound();
    }
}
