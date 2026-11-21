<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class TaskAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function makeLogin(): User
    {
        return User::query()->create([
            'username' => 'login.user', 'full_name' => 'Login User',
            'email' => 'login@example.com', 'password' => 'Testing123!',
        ]);
    }

    public function test_login_and_registration_pages_are_available_to_guests(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk()->assertSee('name="username"', false)->assertSee('name="full_name"', false);
    }

    public function test_registration_creates_the_task_account_fields_and_a_hashed_password(): void
    {
        $this->post('/register', [
            'username' => 'registered', 'full_name' => 'Registered User',
            'email' => 'registered@example.com', 'password' => 'Testing123!',
            'password_confirmation' => 'Testing123!', 'avatar' => '../../.env',
        ])->assertRedirect(route('dashboard'));

        $user = User::query()->where('username', 'registered')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('Registered User', $user->full_name);
        $this->assertTrue(Hash::check('Testing123!', $user->password));
        $this->assertNotSame('Testing123!', $user->password);
        $this->assertSame(['username', 'full_name', 'email', 'password'], $user->getFillable());
    }

    public function test_registration_validates_required_fields_and_keeps_passwords_out_of_old_input(): void
    {
        $this->from('/register')->post('/register', [
            'username' => 'kept', 'email' => 'invalid',
            'password' => 'short', 'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['full_name', 'email', 'password'])
            ->assertSessionHasInput('username', 'kept')
            ->assertSessionMissing('_old_input.password')
            ->assertSessionMissing('_old_input.password_confirmation');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_registration_rejects_duplicate_username_and_email(): void
    {
        $existing = $this->makeLogin();
        $this->post('/register', [
            'username' => $existing->username, 'full_name' => 'Duplicate',
            'email' => $existing->email, 'password' => 'Testing123!',
            'password_confirmation' => 'Testing123!',
        ])->assertSessionHasErrors(['username', 'email']);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_login_returns_to_the_originally_requested_protected_page(): void
    {
        $user = $this->makeLogin();
        $this->get('/tasks/create')->assertRedirect(route('login'));
        $this->post('/login', ['email' => $user->email, 'password' => 'Testing123!'])
            ->assertRedirect(route('tasks.create'));
        $this->assertAuthenticatedAs($user);
        $this->get('/tasks/create')->assertOk();
    }

    public function test_invalid_password_is_rejected(): void
    {
        $user = $this->makeLogin();
        $this->post('/login', ['email' => $user->email, 'password' => 'WrongPassword!'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_a_legacy_user_without_a_password_cannot_log_in(): void
    {
        User::query()->create(['username' => 'legacy', 'full_name' => 'Legacy User', 'email' => 'legacy@example.com']);
        $this->post('/login', ['email' => 'legacy@example.com', 'password' => 'Anything123!'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_regenerates_the_session_identifier(): void
    {
        $user = $this->makeLogin();
        $this->withSession(['kept_marker' => 'present']);
        $oldId = session()->getId();
        $this->post('/login', ['email' => $user->email, 'password' => 'Testing123!'])
            ->assertSessionHas('kept_marker', 'present');
        $this->assertNotSame($oldId, session()->getId());
        $this->assertAuthenticatedAs($user);
    }

    public function test_logout_invalidates_the_session_and_blocks_protected_pages(): void
    {
        $this->actingAs($this->makeLogin())->withSession(['private_marker' => 'remove']);
        $this->post('/logout')->assertRedirect('/')->assertSessionMissing('private_marker');
        $this->assertGuest();
        $this->get('/tasks/create')->assertRedirect(route('login'));
    }

    public function test_logout_is_not_a_get_action(): void
    {
        $this->actingAs($this->makeLogin());
        $this->get('/logout')->assertStatus(405);
    }

    public function test_breeze_login_throttling_remains_enabled(): void
    {
        Event::fake([Lockout::class]);
        $user = $this->makeLogin();
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'WrongPassword!'])
                ->assertSessionHasErrors('email');
        }
        Event::assertDispatched(Lockout::class);
        $this->assertGuest();
    }

    public function test_password_reset_uses_a_token_and_hashes_the_new_password(): void
    {
        Notification::fake();
        $user = $this->makeLogin();
        $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect('/forgot-password')->assertSessionHas('status');
        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token): bool {
            $token = $notification->token;
            return true;
        });
        $this->assertNotNull($token);
        $this->post('/reset-password', [
            'token' => $token, 'email' => $user->email,
            'password' => 'ResetPassword123!', 'password_confirmation' => 'ResetPassword123!',
        ])->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('ResetPassword123!', $user->fresh()->password));
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_an_expired_reset_token_is_rejected(): void
    {
        $user = $this->makeLogin();
        $token = Password::createToken($user);
        DB::table('password_reset_tokens')->where('email', $user->email)
            ->update(['created_at' => now()->subMinutes(61)]);
        $this->post('/reset-password', [
            'token' => $token, 'email' => $user->email,
            'password' => 'ResetPassword123!', 'password_confirmation' => 'ResetPassword123!',
        ])->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('Testing123!', $user->fresh()->password));
    }

    public function test_default_password_update_requires_the_current_password(): void
    {
        $user = $this->makeLogin();
        $this->actingAs($user)->put('/password', [
            'current_password' => 'WrongPassword!',
            'password' => 'ChangedPassword123!', 'password_confirmation' => 'ChangedPassword123!',
        ])->assertSessionHasErrorsIn('updatePassword', ['current_password']);
        $this->assertTrue(Hash::check('Testing123!', $user->fresh()->password));
    }


    public function test_public_profile_remains_the_demo_profile_after_another_account_logs_in(): void
    {
        $this->seed();
        $other = $this->makeLogin();
        $this->actingAs($other)->get('/profile')->assertOk()->assertSee('gian.mistica')->assertDontSee('login@example.com');
        $this->assertArrayNotHasKey('password', $other->toArray());
        $this->assertArrayNotHasKey('remember_token', $other->toArray());
    }
    public function test_existing_demo_password_can_be_set_without_an_extra_user(): void
    {
        $this->seed();
        $this->artisan('tsa4:set-password', ['username' => 'gian.mistica'])
            ->expectsQuestion('New password (at least 8 characters)', 'Testing123!')
            ->expectsQuestion('Confirm new password', 'Testing123!')
            ->assertExitCode(0);
        $this->assertDatabaseCount('users', 1);
        $demo = User::query()->firstOrFail();
        $this->assertTrue(Hash::check('Testing123!', $demo->password));
        $this->post('/login', ['email' => $demo->email, 'password' => 'Testing123!']);
        $this->assertAuthenticatedAs($demo);
    }
    public function test_password_command_rejects_mismatched_passwords_without_changes(): void
    {
        $this->seed();
        $this->artisan('tsa4:set-password', ['username' => 'gian.mistica'])
            ->expectsQuestion('New password (at least 8 characters)', 'Testing123!')
            ->expectsQuestion('Confirm new password', 'Different123!')
            ->assertExitCode(1);
        $this->assertNull(User::query()->firstOrFail()->password);
        $this->assertDatabaseCount('users', 1);
    }
}
