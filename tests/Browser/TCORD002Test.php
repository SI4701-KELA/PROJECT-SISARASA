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
 * TC-ORD-002 | PBI 26 - Manajemen Pesanan Masuk (Seller)
 *
 * Tujuan     : Memverifikasi bahwa navigasi Tab menyaring pesanan sesuai statusnya.
 * Prasyarat  : Terdapat pesanan berstatus menunggu_verifikasi, diproses, siap_diambil.
 * Expected   : Setiap Tab menampilkan pesanan dengan status yang benar.
 * Hasil      : Pass
 * Tanggal    : 10/06/2026
 */
class TCORD002Test extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer ORD002', 'email' => 'buyer_ord002@test.com',
            'role' => 'buyer', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerUser = User::factory()->create([
            'name' => 'Seller ORD002', 'email' => 'seller_ord002@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id, 'store_name' => 'Toko ORD002',
            'address' => 'Jl. ORD002', 'verification_status' => 'approved',
        ]);
        $category = Category::firstOrCreate(['name' => 'Makanan']);
        $product = Product::create([
            'seller_id' => $seller->id, 'category_id' => $category->id,
            'name' => 'Menu ORD002', 'base_price' => 20000,
        ]);
        Stock::create(['product_id' => $product->id, 'qty_reg' => 10, 'qty_surplus' => 0]);

        $orderBaru = Order::create([
            'buyer_id' => $buyer->id, 'seller_id' => $seller->id,
            'total_amount' => 20000, 'payment_method' => 'cash', 'status' => 'menunggu_verifikasi',
        ]);
        OrderItem::create(['order_id' => $orderBaru->id, 'product_id' => $product->id, 'qty' => 1, 'price' => 20000, 'is_surplus' => false]);

        $orderDiproses = Order::create([
            'buyer_id' => $buyer->id, 'seller_id' => $seller->id,
            'total_amount' => 20000, 'payment_method' => 'cash', 'status' => 'diproses',
        ]);
        OrderItem::create(['order_id' => $orderDiproses->id, 'product_id' => $product->id, 'qty' => 1, 'price' => 20000, 'is_surplus' => false]);

        $orderSiap = Order::create([
            'buyer_id' => $buyer->id, 'seller_id' => $seller->id,
            'total_amount' => 20000, 'payment_method' => 'cash', 'status' => 'siap_diambil',
        ]);
        OrderItem::create(['order_id' => $orderSiap->id, 'product_id' => $product->id, 'qty' => 1, 'price' => 20000, 'is_surplus' => false]);

        return compact('sellerUser', 'orderBaru', 'orderDiproses', 'orderSiap');
    }

    public function test_tab_navigasi_menyaring_pesanan_sesuai_status(): void
    {
        $eco = $this->setupEcosystem();
        $this->browse(function (Browser $browser) use ($eco) {
            $browser->loginAs($eco['sellerUser']);

            // Tab Pesanan Baru
            $browser->visitRoute('seller.orders', ['tab' => 'baru'])->pause(1000)
                ->assertSee('#' . $eco['orderBaru']->id)
                ->assertDontSee('#' . $eco['orderDiproses']->id)
                ->assertDontSee('#' . $eco['orderSiap']->id)
                ->screenshot('tc_ord_002_tab_baru');

            // Tab Diproses (klik tab link)
            $browser->click('#tab-diproses')->pause(1000)
                ->assertSee('#' . $eco['orderDiproses']->id)
                ->assertDontSee('#' . $eco['orderBaru']->id)
                ->assertDontSee('#' . $eco['orderSiap']->id)
                ->screenshot('tc_ord_002_tab_diproses');

            // Tab Siap Diambil
            $browser->click('#tab-siap')->pause(1000)
                ->assertSee('#' . $eco['orderSiap']->id)
                ->assertDontSee('#' . $eco['orderBaru']->id)
                ->assertDontSee('#' . $eco['orderDiproses']->id)
                ->screenshot('tc_ord_002_tab_siap');
        });
    }
}
