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
 * TC-ETA-006 | PBI 17 - Pesanan Hangus
 *
 * Tujuan     : Memverifikasi bahwa pesanan dengan status "Siap Diambil" yang melewati
 *              batas waktu pengambilan (pickup_deadline) otomatis berubah menjadi "Hangus"
 *              saat pembeli membuka halaman detail pesanan.
 * Prasyarat  : Pesanan berstatus "siap_diambil" dengan pickup_deadline yang sudah lewat.
 * Expected   : Status berubah ke "hangus", stok dikembalikan, dan UI menampilkan banner
 *              "Pesanan Hangus" beserta alasan.
 * Tanggal    : 21/06/2026
 */
class TCETA006Test extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer ETA006', 'email' => 'buyer_eta006@test.com',
            'role' => 'buyer', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerUser = User::factory()->create([
            'name' => 'Seller ETA006', 'email' => 'seller_eta006@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id, 'store_name' => 'Toko ETA006',
            'address' => 'Jl. ETA006', 'verification_status' => 'approved',
            'close_time' => '23:00',
        ]);
        $category = Category::firstOrCreate(['name' => 'Makanan']);
        $product = Product::create([
            'seller_id' => $seller->id, 'category_id' => $category->id,
            'name' => 'Menu ETA006', 'base_price' => 20000,
        ]);
        $stock = Stock::create(['product_id' => $product->id, 'qty_reg' => 10, 'qty_surplus' => 0]);

        // Pesanan siap_diambil dengan pickup_deadline sudah lewat 1 jam
        $order = Order::create([
            'buyer_id' => $buyer->id, 'seller_id' => $seller->id,
            'total_amount' => 20000, 'payment_method' => 'cash',
            'status' => 'siap_diambil', 'pickup_deadline' => now()->subHour(),
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $product->id,
            'qty' => 2, 'price' => 20000, 'is_surplus' => false,
        ]);

        return compact('buyer', 'order', 'stock', 'product');
    }

    public function test_pesanan_hangus_otomatis_saat_batas_waktu_lewat(): void
    {
        $eco = $this->setupEcosystem();

        // Sebelum membuka halaman, status masih siap_diambil
        $this->assertEquals('siap_diambil', $eco['order']->fresh()->status);

        $this->browse(function (Browser $browser) use ($eco) {
            $browser->loginAs($eco['buyer'])
                ->visitRoute('buyer.orders.show', $eco['order']->id)
                ->pause(1500)
                ->assertSee('Pesanan Hangus')
                ->assertSee('tidak diambil dalam batas waktu')
                ->screenshot('tc_eta_006_pesanan_hangus');
        });

        // Verifikasi di database: status berubah ke hangus
        $eco['order']->refresh();
        $this->assertEquals('hangus', $eco['order']->status);
        $this->assertNotEmpty($eco['order']->cancellation_reason);

        // Verifikasi stok dikembalikan (10 + 2 = 12)
        $eco['stock']->refresh();
        $this->assertEquals(12, $eco['stock']->qty_reg);
    }
}
