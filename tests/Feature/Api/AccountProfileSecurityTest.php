<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_profile_requires_authentication(): void
    {
        $this->patchJson('/api/account/profile', ['name' => 'X'])
            ->assertUnauthorized();
    }

    public function test_api_profile_updates_from_session_user(): void
    {
        $user = User::factory()->create(['name' => 'Asal']);

        $this->actingAs($user)
            ->patchJson('/api/account/profile', ['name' => 'Diperbarui'])
            ->assertOk()
            ->assertJsonPath('user.name', 'Diperbarui');

        $this->assertSame('Diperbarui', $user->refresh()->name);
    }

    public function test_api_password_rejects_wrong_current_password_with_401(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password-saya'),
        ]);

        $this->actingAs($user)
            ->postJson('/api/account/password', [
                'current_password' => 'salah',
                'password' => 'baru12345',
                'password_confirmation' => 'baru12345',
            ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Kata sandi lama salah.');
    }

    public function test_api_password_rejects_short_new_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/account/password', [
                'current_password' => 'password',
                'password' => 'pendek',
                'password_confirmation' => 'pendek',
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['message' => 'Kata sandi baru minimal harus 8 karakter.']);
    }

    public function test_api_profile_accepts_multipart_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'photo' => 'profiles/lama.jpg',
        ]);
        Storage::disk('public')->put('profiles/lama.jpg', 'x');

        $file = UploadedFile::fake()->image('baru.jpg');

        $this->actingAs($user)
            ->patch('/api/account/profile', [
                'name' => $user->name,
                'photo' => $file,
            ])
            ->assertOk();

        Storage::disk('public')->assertMissing('profiles/lama.jpg');
        $this->assertNotNull($user->refresh()->photo);
    }
}
