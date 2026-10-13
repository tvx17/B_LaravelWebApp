<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function verificationUrl(User $user, string $route = 'verification.verify'): string
    {
        return URL::temporarySignedRoute($route, now()->addMinutes(60), [
            'id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification()),
        ]);
    }

    public function test_unverified_users_see_the_notice_instead_of_the_dashboard(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->get('/dashboard')->assertRedirect('/email/verify');
        $this->get('/email/verify')->assertOk()->assertSee('E-Mail bestätigen');
        $this->getJson('/api/email-verification/status')->assertExactJson(['verified' => false]);
    }

    public function test_signed_link_confirms_email_and_dispatches_verified_only_once(): void
    {
        Event::fake([Verified::class]);
        $user = User::factory()->unverified()->create();
        $url = $this->verificationUrl($user);
        $this->actingAs($user)->get($url)->assertRedirect('/dashboard?verified=1');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->get($url)->assertRedirect('/dashboard?verified=1');
        Event::assertDispatchedTimes(Verified::class, 1);
        $this->get('/dashboard')->assertOk();
    }

    public function test_api_verification_accepts_only_valid_links_for_the_current_user(): void
    {
        $user = User::factory()->unverified()->create();
        $other = User::factory()->unverified()->create();
        $this->actingAs($user)->getJson($this->verificationUrl($other, 'api.verification.verify'))->assertForbidden();
        $url = $this->verificationUrl($user, 'api.verification.verify');
        $this->getJson($url.'&tampered=1')->assertForbidden();
        $expired = URL::temporarySignedRoute('api.verification.verify', now()->subMinute(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->getJson($expired)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $this->getJson($url)->assertExactJson(['verified' => true]);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_resending_requires_authentication_and_is_throttled(): void
    {
        Notification::fake();
        $this->getJson('/api/email-verification/status')->assertUnauthorized();
        $this->postJson('/api/email-verification/notification')->assertUnauthorized();
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->postJson('/api/email-verification/notification')->assertOk()->assertJsonPath('status', 'verification-link-sent');
        }
        $this->postJson('/api/email-verification/notification')->assertStatus(429);
        Notification::assertSentToTimes($user, VerifyEmail::class, 6);
    }

    public function test_verified_users_are_not_sent_additional_mail(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/api/email-verification/notification')->assertExactJson(['status' => 'already-verified']);
        Notification::assertNothingSent();
    }

    public function test_session_api_keeps_csrf_protection(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        $this->app['env'] = 'local';
        $this->actingAs($user)->withSession(['_token' => 'valid-token'])
            ->postJson('/api/email-verification/notification', ['_token' => 'wrong-token'])->assertStatus(419);
        Notification::assertNothingSent();
    }

    public function test_actual_notification_link_works_with_fortify(): void
    {
        $user = User::factory()->unverified()->create();
        $mail = (new VerifyEmail)->toMail($user);
        $this->actingAs($user)->get($mail->actionUrl)->assertRedirect('/dashboard?verified=1');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_panel_requires_verified_email_even_with_admin_permission(): void
    {
        $user = User::factory()->unverified()->create();
        $user->givePermissionTo(Permission::findOrCreate('access-admin', 'web'));
        $this->actingAs($user)->get('/admin')->assertRedirect(Filament::getPanel('admin')->getEmailVerificationPromptUrl());
    }

    public function test_user_creation_sends_the_native_verification_notification(): void
    {
        Notification::fake();
        $this->artisan('app:create-user', ['email' => 'new@example.com', '--name' => 'New'])
            ->expectsQuestion('Passwort (mindestens 12 Zeichen)', 'a-long-test-password')
            ->expectsQuestion('Passwort wiederholen', 'a-long-test-password')->assertSuccessful();
        $user = User::where('email', 'new@example.com')->sole();
        $this->assertFalse($user->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
