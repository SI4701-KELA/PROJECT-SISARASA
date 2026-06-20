<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC-ORD-001 | PBI 26 - Manajemen Pesanan Masuk (Seller)
 *
 * Tujuan     : Memverifikasi bahwa penjual hanya dapat melihat pesanan yang masuk
 *              ke tokonya sendiri, dan tidak dapat melihat pesanan toko lain.
 * Prasyarat  : Login sebagai Penjual A. Di database terdapat pesanan untuk Toko A dan Toko B.
 * Expected   : Daftar hanya menampilkan pesanan milik Toko A. Pesanan Toko B tidak muncul.
 * Hasil      : Pass
 * Tanggal    : 10/06/2026
 */
class TCORD001Test extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer ORD001', 'email' => 'buyer_ord001@test.com',
            'role' => 'buyer', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $category = Category::firstOrCreate(['name' => 'Makanan']);

        // Toko A
        $sellerUserA = User::factory()->create([
            'name' => 'Seller A', 'email' => 'seller_a_ord001@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerA = Seller::create([
            'user_id' => $sellerUserA->id, 'store_name' => 'Toko A ORD001',
            'address' => 'Jl. A', 'verification_status' => 'approved',
        ]);
        $productA = Product::create([
            'seller_id' => $sellerA->id, 'category_id' => $category->id,
            'name' => 'Menu A', 'base_price' => 20000,
        ]);
        Stock::create(['product_id' => $productA->id, 'qty_reg' => 10, 'qty_surplus' => 0]);

        // Toko B
        $sellerUserB = User::factory()->create([
            'name' => 'Seller B', 'email' => 'seller_b_ord001@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerB = Seller::create([
            'user_id' => $sellerUserB->id, 'store_name' => 'Toko B ORD001',
            'address' => 'Jl. B', 'verification_status' => 'approved',
        ]);
        $productB = Product::create([
            'seller_id' => $sellerB->id, 'category_id' => $category->id,
            'name' => 'Menu B', 'base_price' => 25000,
        ]);
        Stock::create(['product_id' => $productB->id, 'qty_reg' => 10, 'qty_surplus' => 0]);

        // Pesanan Toko A
        $orderA = Order::create([
            'buyer_id' => $buyer->id, 'seller_id' => $sellerA->id,
            'total_amount' => 20000, 'payment_method' => 'cash', 'status' => 'menunggu_verifikasi',
        ]);
        OrderItem::create(['order_id' => $orderA->id, 'product_id' => $productA->id, 'qty' => 1, 'price' => 20000, 'is_surplus' => false]);

        // Pesanan Toko B
        $orderB = Order::create([
            'buyer_id' => $buyer->id, 'seller_id' => $sellerB->id,
            'total_amount' => 25000, 'payment_method' => 'cash', 'status' => 'menunggu_verifikasi',
        ]);
        OrderItem::create(['order_id' => $orderB->id, 'product_id' => $productB->id, 'qty' => 1, 'price' => 25000, 'is_surplus' => false]);

        return compact('sellerUserA', 'orderA', 'orderB');
    }

    public function test_seller_hanya_melihat_pesanan_tokonya_sendiri(): void
    {
        $eco = $this->setupEcosystem();
        $this->browse(function (Browser $browser) use ($eco) {
            $browser->loginAs($eco['sellerUserA'])
                ->visitRoute('seller.orders', ['tab' => 'baru'])
                ->pause(1000)
                ->assertSee('#' . $eco['orderA']->id)
                ->assertDontSee('#' . $eco['orderB']->id)
                ->screenshot('tc_ord_001_seller_only_own_orders');
        });
    }
}
