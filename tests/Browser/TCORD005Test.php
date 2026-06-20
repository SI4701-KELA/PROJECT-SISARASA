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
 * TC-ORD-005 | PBI 26 - Manajemen Pesanan Masuk (Seller) — Negative Test
 *
 * Tujuan     : Memverifikasi bahwa sistem menolak penolakan apabila kolom alasan dikosongkan.
 * Prasyarat  : Penjual berada di modal aksi pesanan QRIS.
 * Expected   : Sistem menolak aksi. Status pesanan tidak berubah.
 * Hasil      : Pass
 * Tanggal    : 10/06/2026
 */
class TCORD005Test extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer ORD005', 'email' => 'buyer_ord005@test.com',
            'role' => 'buyer', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerUser = User::factory()->create([
            'name' => 'Seller ORD005', 'email' => 'seller_ord005@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id, 'store_name' => 'Toko ORD005',
            'address' => 'Jl. ORD005', 'verification_status' => 'approved',
        ]);
        $category = Category::firstOrCreate(['name' => 'Makanan']);
        $product = Product::create([
            'seller_id' => $seller->id, 'category_id' => $category->id,
            'name' => 'Menu ORD005', 'base_price' => 20000,
        ]);
        Stock::create(['product_id' => $product->id, 'qty_reg' => 10, 'qty_surplus' => 0]);

        $order = Order::create([
            'buyer_id' => $buyer->id, 'seller_id' => $seller->id,
            'total_amount' => 20000, 'payment_method' => 'qris',
            'payment_proof' => 'payments/bukti.jpg', 'status' => 'menunggu_verifikasi',
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'qty' => 1, 'price' => 20000, 'is_surplus' => false]);

        return compact('sellerUser', 'order');
    }

    public function test_tolak_pembayaran_dengan_alasan_kosong_ditolak(): void
    {
        $eco = $this->setupEcosystem();
        $this->browse(function (Browser $browser) use ($eco) {
            $browser->loginAs($eco['sellerUser'])
                ->visitRoute('seller.orders', ['tab' => 'baru'])->pause(1000)
                ->click('.btn-tolak')
                ->waitFor('#reject-modal')->pause(500)
                // Jangan pilih alasan — tombol submit harus disabled via Alpine :disabled
                ->assertDisabled('.btn-submit-tolak')
                ->screenshot('tc_ord_005_reject_empty_reason_disabled');
        });

        // Verifikasi status pesanan TIDAK berubah
        $eco['order']->refresh();
        $this->assertEquals('menunggu_verifikasi', $eco['order']->status);
    }
}
