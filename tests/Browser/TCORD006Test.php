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
 * TC-ORD-006 | PBI 26 - Manajemen Pesanan Masuk (Seller)
 *
 * Tujuan     : Memverifikasi alur penolakan pembayaran QRIS dengan alasan valid:
 *              status berubah ke Dibatalkan dan alasan tersimpan di DB.
 * Prasyarat  : Penjual berada di modal aksi pesanan QRIS.
 * Input      : Teks Alasan: "Nominal kurang"
 * Expected   : Status berubah menjadi "Dibatalkan". Alasan tersimpan di cancellation_reason.
 * Hasil      : Pass
 * Tanggal    : 10/06/2026
 */
class TCORD006Test extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer ORD006', 'email' => 'buyer_ord006@test.com',
            'role' => 'buyer', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerUser = User::factory()->create([
            'name' => 'Seller ORD006', 'email' => 'seller_ord006@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id, 'store_name' => 'Toko ORD006',
            'address' => 'Jl. ORD006', 'verification_status' => 'approved',
        ]);
        $category = Category::firstOrCreate(['name' => 'Makanan']);
        $product = Product::create([
            'seller_id' => $seller->id, 'category_id' => $category->id,
            'name' => 'Menu ORD006', 'base_price' => 20000,
        ]);
        $stock = Stock::create(['product_id' => $product->id, 'qty_reg' => 9, 'qty_surplus' => 0]);

        $order = Order::create([
            'buyer_id' => $buyer->id, 'seller_id' => $seller->id,
            'total_amount' => 20000, 'payment_method' => 'qris',
            'payment_proof' => 'payments/bukti.jpg', 'status' => 'menunggu_verifikasi',
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'qty' => 1, 'price' => 20000, 'is_surplus' => false]);

        return compact('sellerUser', 'order', 'stock');
    }

    public function test_tolak_pembayaran_dengan_alasan_valid_berhasil(): void
    {
        $eco = $this->setupEcosystem();
        $this->browse(function (Browser $browser) use ($eco) {
            $browser->loginAs($eco['sellerUser'])
                ->visitRoute('seller.orders', ['tab' => 'baru'])->pause(1000)
                ->click('.btn-tolak')
                ->waitFor('#reject-modal')->pause(500)
                // Pilih "Lainnya" dan isi alasan kustom "Nominal kurang"
                ->select('#cancellation_reason_dropdown', 'Lainnya')
                ->pause(300)
                ->type('#cancellation_reason_other', 'Nominal kurang')
                ->pause(300)
                ->click('.btn-submit-tolak')
                ->waitForText('Pembayaran ditolak')
                ->assertSee('Pembayaran ditolak')
                ->screenshot('tc_ord_006_reject_payment_success');
        });

        // Verifikasi di database
        $eco['order']->refresh();
        $this->assertEquals('dibatalkan', $eco['order']->status);
        $this->assertEquals('Nominal kurang', $eco['order']->cancellation_reason);

        // Verifikasi stok dikembalikan (9 + 1 = 10)
        $eco['stock']->refresh();
        $this->assertEquals(10, $eco['stock']->qty_reg);
    }
}
