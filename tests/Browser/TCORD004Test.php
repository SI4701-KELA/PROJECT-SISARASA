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
 * TC-ORD-004 | PBI 26 - Manajemen Pesanan Masuk (Seller)
 *
 * Tujuan     : Memverifikasi alur penerimaan pembayaran QRIS: status berubah ke "Diproses".
 * Prasyarat  : Pesanan QRIS terlihat di Tab Pesanan Baru.
 * Expected   : Status pesanan diubah ke "Diproses" dan pindah ke Tab Sedang Diproses.
 * Hasil      : Pass
 * Tanggal    : 10/06/2026
 */
class TCORD004Test extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer ORD004', 'email' => 'buyer_ord004@test.com',
            'role' => 'buyer', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerUser = User::factory()->create([
            'name' => 'Seller ORD004', 'email' => 'seller_ord004@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id, 'store_name' => 'Toko ORD004',
            'address' => 'Jl. ORD004', 'verification_status' => 'approved',
        ]);
        $category = Category::firstOrCreate(['name' => 'Makanan']);
        $product = Product::create([
            'seller_id' => $seller->id, 'category_id' => $category->id,
            'name' => 'Menu ORD004', 'base_price' => 20000,
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

    public function test_terima_pembayaran_mengubah_status_ke_diproses(): void
    {
        $eco = $this->setupEcosystem();
        $this->browse(function (Browser $browser) use ($eco) {
            $browser->loginAs($eco['sellerUser'])
                ->visitRoute('seller.orders', ['tab' => 'baru'])->pause(1000)
                ->assertSee('#' . $eco['order']->id)
                ->click('.btn-terima')
                ->waitForText('Pembayaran diterima')
                ->assertSee('Pembayaran diterima')
                ->screenshot('tc_ord_004_terima_pembayaran');
        });

        $eco['order']->refresh();
        $this->assertEquals('diproses', $eco['order']->status);
    }
}
