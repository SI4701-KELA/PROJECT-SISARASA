<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_profile_phone_can_be_updated_separately(): void
    {
        $user = User::factory()->create(['phone' => null]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'phone' => '081234567890',
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $this->assertSame('081234567890', $user->refresh()->phone);
    }

    public function test_profile_photo_replaces_old_file_after_save(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'photo' => 'profiles/old.jpg',
        ]);
        Storage::disk('public')->put('profiles/old.jpg', 'fake');

        $new = UploadedFile::fake()->image('new.jpg', 100, 100);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'photo' => $new,
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $user->refresh();
        $this->assertNotSame('profiles/old.jpg', $user->photo);
        Storage::disk('public')->assertMissing('profiles/old.jpg');
        Storage::disk('public')->assertExists($user->photo);
    }

    public function test_email_cannot_be_changed_via_profile_update(): void
    {
        $user = User::factory()->create(['email' => 'keep@example.com']);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'New Name',
                'email' => 'hacker@example.com',
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame('keep@example.com', $user->refresh()->email);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/login');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
