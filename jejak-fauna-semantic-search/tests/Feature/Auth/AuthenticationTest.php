<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_persists_a_hashed_password_and_authenticates_the_new_user(): void
    {
        $response = $this->post(route('register.store'), $this->registrationData(
            'Nadia Uji',
            'NADIA@example.test',
        ));

        $response->assertRedirect(route('profile'));
        $this->assertDatabaseHas('users', [
            'name' => 'Nadia Uji',
            'email' => 'nadia@example.test',
        ]);

        $user = User::query()->where('email', 'nadia@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('fauna-safe-password', $user->password));
        $this->assertAuthenticatedAs($user);
        $this->get(route('profile'))
            ->assertOk()
            ->assertSee('Nadia Uji')
            ->assertSee('nadia@example.test')
            ->assertDontSee('Ayu Lestari');
    }

    public function test_registration_rejects_an_existing_email_case_insensitively(): void
    {
        User::factory()->create(['email' => 'existing@example.test']);

        $response = $this->post(route('register.store'), $this->registrationData(
            'Pengguna Baru',
            'EXISTING@example.test',
        ));

        $response->assertInvalid(['email' => 'Alamat email sudah terdaftar.']);
        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_registration_rejects_a_mismatched_password_confirmation(): void
    {
        $response = $this->post(route('register.store'), [
            ...$this->registrationData('Pengguna Baru', 'baru@example.test'),
            'password_confirmation' => 'different-password',
        ]);

        $response->assertInvalid(['password_confirmation' => 'Konfirmasi kata sandi tidak cocok.']);
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_registration_requires_consent(): void
    {
        $payload = $this->registrationData('Pengguna Baru', 'baru@example.test');
        unset($payload['consent']);

        $response = $this->post(route('register.store'), $payload);

        $response->assertInvalid(['consent' => 'Persetujuan wajib diberikan.']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_login_authenticates_the_matching_email_and_password(): void
    {
        $user = $this->createUser('Nadia Uji', 'nadia@example.test', 'fauna-login-password');

        $response = $this->post(route('login.store'), [
            'email' => 'NADIA@example.test',
            'password' => 'fauna-login-password',
        ]);

        $response->assertRedirect(route('profile'));
        $this->assertAuthenticatedAs($user);
        $this->get(route('profile'))->assertSee('Nadia Uji')->assertSee('nadia@example.test');
    }

    public function test_login_rejects_an_incorrect_password_without_authenticating(): void
    {
        $this->createUser('Nadia Uji', 'nadia@example.test', 'fauna-login-password');

        $response = $this->post(route('login.store'), [
            'email' => 'nadia@example.test',
            'password' => 'incorrect-password',
        ]);

        $response->assertInvalid(['email' => 'Email atau kata sandi tidak valid.']);
        $this->assertGuest();
    }

    public function test_two_accounts_render_the_identity_of_the_active_user(): void
    {
        $firstUser = $this->createUser('Pengguna Pertama', 'pertama@example.test', 'first-password');
        $secondUser = $this->createUser('Pengguna Kedua', 'kedua@example.test', 'second-password');

        $this->post(route('login.store'), [
            'email' => $firstUser->email,
            'password' => 'first-password',
        ])->assertRedirect(route('profile'));

        $this->get(route('home'))
            ->assertSee('Pengguna Pertama')
            ->assertDontSee('Pengguna Kedua');
        $this->get(route('profile'))
            ->assertSee('Pengguna Pertama')
            ->assertDontSee('Ayu Lestari');

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();

        $this->post(route('login.store'), [
            'email' => $secondUser->email,
            'password' => 'second-password',
        ])->assertRedirect(route('profile'));

        $this->assertNotSame($firstUser->id, $secondUser->id);
        $this->get(route('profile'))
            ->assertSee('Pengguna Kedua')
            ->assertDontSee('Pengguna Pertama')
            ->assertDontSee('Ayu Lestari');
    }

    public function test_guests_are_redirected_from_profile_and_admin_pages(): void
    {
        $this->get(route('profile'))->assertRedirect(route('login'));
        $this->get(route('admin.reports'))->assertRedirect(route('login'));
    }

    public function test_logout_ends_authentication_and_blocks_the_previous_session(): void
    {
        $user = $this->createUser('Nadia Uji', 'nadia@example.test', 'fauna-login-password');
        $this->actingAs($user);

        $this->post(route('logout'))->assertRedirect(route('home'));

        $this->assertGuest();
        $this->get(route('profile'))->assertRedirect(route('login'));
    }

    /**
     * @return array{name: string, email: string, password: string, password_confirmation: string, consent: string}
     */
    private function registrationData(string $name, string $email): array
    {
        return [
            'name' => $name,
            'email' => $email,
            'password' => 'fauna-safe-password',
            'password_confirmation' => 'fauna-safe-password',
            'consent' => '1',
        ];
    }

    private function createUser(string $name, string $email, string $password): User
    {
        return User::factory()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }
}
