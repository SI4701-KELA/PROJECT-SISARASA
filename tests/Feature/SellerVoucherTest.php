<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerVoucherTest extends TestCase
{
    use RefreshDatabase;

    private function createSeller(string $status = 'approved'): array
    {
        $user = User::factory()->create(['role' => 'seller']);
        $seller = Seller::create([
            'user_id' => $user->id,
            'store_name' => 'Warung Test',
            'address' => 'Jl. Test',
            'verification_status' => $status,
        ]);
        return compact('user', 'seller');
    }

    public function test_unverified_seller_cannot_access_vouchers(): void
    {
        $eco = $this->createSeller('pending');

        $response = $this->actingAs($eco['user'])
            ->get(route('seller.vouchers.index'));

        $response->assertRedirect(route('seller.profile'));
        $response->assertSessionHas('error');
    }

    public function test_verified_seller_can_list_their_vouchers(): void
    {
        $eco = $this->createSeller('approved');

        $voucher = Voucher::create([
            'seller_id' => $eco['seller']->id,
            'code' => 'WARUNG10',
            'type' => 'percent',
            'value' => 10,
            'min_order' => 20000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($eco['user'])
            ->get(route('seller.vouchers.index'));

        $response->assertStatus(200);
        $response->assertSee('WARUNG10');
    }

    public function test_seller_can_create_voucher_with_validations(): void
    {
        $eco = $this->createSeller('approved');

        // Test creation success
        $response = $this->actingAs($eco['user'])
            ->post(route('seller.vouchers.store'), [
                'code' => 'hemat20', // Case sanitization: will be uppercase
                'type' => 'percent',
                'value' => 20,
                'min_order' => 15000,
                'is_active' => '1',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vouchers', [
            'seller_id' => $eco['seller']->id,
            'code' => 'HEMAT20',
            'type' => 'percent',
            'value' => 20,
            'min_order' => 15000,
            'is_active' => true,
        ]);

        // Test validation: percentage cannot exceed 100%
        $response2 = $this->actingAs($eco['user'])
            ->post(route('seller.vouchers.store'), [
                'code' => 'OVER100',
                'type' => 'percent',
                'value' => 110,
                'min_order' => 1000,
            ]);

        $response2->assertSessionHasErrors(['value']);

        // Test validation: duplicate code
        $response3 = $this->actingAs($eco['user'])
            ->post(route('seller.vouchers.store'), [
                'code' => 'hemat20',
                'type' => 'fixed',
                'value' => 10000,
                'min_order' => 0,
            ]);

        $response3->assertSessionHasErrors(['code']);
    }

    public function test_seller_can_update_their_voucher(): void
    {
        $eco = $this->createSeller('approved');
        $voucher = Voucher::create([
            'seller_id' => $eco['seller']->id,
            'code' => 'OLDCODE',
            'type' => 'fixed',
            'value' => 5000,
            'min_order' => 10000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($eco['user'])
            ->put(route('seller.vouchers.update', $voucher->id), [
                'code' => 'newcode',
                'type' => 'percent',
                'value' => 15,
                'min_order' => 20000,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vouchers', [
            'id' => $voucher->id,
            'code' => 'NEWCODE',
            'type' => 'percent',
            'value' => 15,
            'min_order' => 20000,
            'is_active' => false, // unchecked is_active defaults to false
        ]);
    }

    public function test_seller_can_toggle_voucher_status(): void
    {
        $eco = $this->createSeller('approved');
        $voucher = Voucher::create([
            'seller_id' => $eco['seller']->id,
            'code' => 'TOGGLEME',
            'type' => 'fixed',
            'value' => 5000,
            'min_order' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($eco['user'])
            ->patch(route('seller.vouchers.toggle-status', $voucher->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('vouchers', [
            'id' => $voucher->id,
            'is_active' => false,
        ]);
    }

    public function test_seller_can_delete_their_voucher(): void
    {
        $eco = $this->createSeller('approved');
        $voucher = Voucher::create([
            'seller_id' => $eco['seller']->id,
            'code' => 'DELETEME',
            'type' => 'fixed',
            'value' => 5000,
            'min_order' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($eco['user'])
            ->delete(route('seller.vouchers.destroy', $voucher->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('vouchers', [
            'id' => $voucher->id,
        ]);
    }

    public function test_seller_cannot_modify_other_sellers_voucher(): void
    {
        $eco1 = $this->createSeller('approved');
        $eco2 = $this->createSeller('approved');

        $voucher = Voucher::create([
            'seller_id' => $eco1['seller']->id,
            'code' => 'SELLERONE',
            'type' => 'fixed',
            'value' => 5000,
            'min_order' => 0,
            'is_active' => true,
        ]);

        // Seller 2 tries to update Seller 1's voucher
        $response = $this->actingAs($eco2['user'])
            ->put(route('seller.vouchers.update', $voucher->id), [
                'code' => 'HACKED',
                'type' => 'fixed',
                'value' => 10000,
                'min_order' => 0,
            ]);

        $response->assertStatus(404);
        $this->assertDatabaseHas('vouchers', [
            'id' => $voucher->id,
            'code' => 'SELLERONE',
        ]);

        // Seller 2 tries to delete Seller 1's voucher
        $response2 = $this->actingAs($eco2['user'])
            ->delete(route('seller.vouchers.destroy', $voucher->id));

        $response2->assertStatus(404);
        $this->assertDatabaseHas('vouchers', [
            'id' => $voucher->id,
        ]);
    }
}
