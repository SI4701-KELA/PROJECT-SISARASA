<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Stock;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function createEcosystem(): array
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Toko Seeder Budi',
            'address' => 'Jl. Budi',
            'verification_status' => 'approved',
            'qris_image' => 'qris/budi.png',
        ]);

        $buyer = User::factory()->create(['role' => 'buyer']);

        $category = Category::create(['name' => 'Makanan']);
        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Nasi Goreng',
            'base_price' => 20000,
            'image' => 'products/nasgor.jpg',
        ]);

        Stock::create([
            'product_id' => $product->id,
            'qty_reg' => 10,
            'qty_surplus' => 5,
        ]);

        return compact('sellerUser', 'seller', 'buyer', 'product');
    }

    public function test_check_voucher_endpoint_validates_correctly(): void
    {
        $eco = $this->createEcosystem();
        
        $voucher = Voucher::create([
            'seller_id' => $eco['seller']->id,
            'code' => 'BUDI10',
            'type' => 'percent',
            'value' => 10,
            'min_order' => 10000,
            'is_active' => true,
        ]);

        // Tambah ke keranjang
        Cart::create([
            'buyer_id' => $eco['buyer']->id,
            'product_id' => $eco['product']->id,
            'qty' => 1,
            'is_surplus' => false,
        ]);

        $response = $this->actingAs($eco['buyer'])
            ->postJson(route('buyer.checkout.check-voucher'), [
                'code' => 'BUDI10',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'discount' => 2000, // 10% dari 20.000
            ]);
    }

    public function test_check_voucher_rejects_other_seller_voucher(): void
    {
        $eco = $this->createEcosystem();
        
        $otherSellerUser = User::factory()->create(['role' => 'seller']);
        $otherSeller = Seller::create([
            'user_id' => $otherSellerUser->id,
            'store_name' => 'Toko Siti',
            'address' => 'Jl. Siti',
            'verification_status' => 'approved',
        ]);

        // Voucher milik Toko Siti
        $voucher = Voucher::create([
            'seller_id' => $otherSeller->id,
            'code' => 'SITI10',
            'type' => 'percent',
            'value' => 10,
            'min_order' => 10000,
            'is_active' => true,
        ]);

        // Tambah ke keranjang produk Toko Budi
        Cart::create([
            'buyer_id' => $eco['buyer']->id,
            'product_id' => $eco['product']->id,
            'qty' => 1,
            'is_surplus' => false,
        ]);

        $response = $this->actingAs($eco['buyer'])
            ->postJson(route('buyer.checkout.check-voucher'), [
                'code' => 'SITI10',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Kode voucher tidak valid untuk toko ini.'
            ]);
    }

    public function test_check_voucher_enforces_min_order(): void
    {
        $eco = $this->createEcosystem();
        
        $voucher = Voucher::create([
            'seller_id' => $eco['seller']->id,
            'code' => 'BUDI50',
            'type' => 'fixed',
            'value' => 5000,
            'min_order' => 30000, // Lebih besar dari harga nasgor 20.000
            'is_active' => true,
        ]);

        Cart::create([
            'buyer_id' => $eco['buyer']->id,
            'product_id' => $eco['product']->id,
            'qty' => 1, // total 20.000
            'is_surplus' => false,
        ]);

        $response = $this->actingAs($eco['buyer'])
            ->postJson(route('buyer.checkout.check-voucher'), [
                'code' => 'BUDI50',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Minimum pembelian Rp 30.000 tidak terpenuhi.'
            ]);
    }

    public function test_checkout_store_applies_percent_voucher_successfully(): void
    {
        $eco = $this->createEcosystem();

        $voucher = Voucher::create([
            'seller_id' => $eco['seller']->id,
            'code' => 'BUDI15',
            'type' => 'percent',
            'value' => 15,
            'min_order' => 10000,
            'is_active' => true,
        ]);

        Cart::create([
            'buyer_id' => $eco['buyer']->id,
            'product_id' => $eco['product']->id,
            'qty' => 2, // Total 40.000
            'is_surplus' => false,
        ]);

        $response = $this->actingAs($eco['buyer'])
            ->post(route('buyer.checkout.store'), [
                'payment_method' => 'cash',
                'promo_code' => 'BUDI15',
            ]);

        // Diskon = 15% dari 40.000 = 6.000. Total akhir = 34.000
        $this->assertDatabaseHas('orders', [
            'buyer_id' => $eco['buyer']->id,
            'total_amount' => 34000,
            'voucher_code' => 'BUDI15',
            'discount_amount' => 6000,
        ]);

        $response->assertRedirect();
    }

    public function test_checkout_store_applies_fixed_voucher_successfully(): void
    {
        $eco = $this->createEcosystem();

        $voucher = Voucher::create([
            'seller_id' => $eco['seller']->id,
            'code' => 'BUDIPOTONG10',
            'type' => 'fixed',
            'value' => 10000,
            'min_order' => 15000,
            'is_active' => true,
        ]);

        Cart::create([
            'buyer_id' => $eco['buyer']->id,
            'product_id' => $eco['product']->id,
            'qty' => 1, // Total 20.000
            'is_surplus' => false,
        ]);

        $response = $this->actingAs($eco['buyer'])
            ->post(route('buyer.checkout.store'), [
                'payment_method' => 'cash',
                'promo_code' => 'BUDIPOTONG10',
            ]);

        // Diskon = 10.000. Total akhir = 10.000
        $this->assertDatabaseHas('orders', [
            'buyer_id' => $eco['buyer']->id,
            'total_amount' => 10000,
            'voucher_code' => 'BUDIPOTONG10',
            'discount_amount' => 10000,
        ]);

        $response->assertRedirect();
    }
}
