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
 * TC-ETA-002 | PBI 17 - Estimasi Waktu Ambil
 *
 * Tujuan     : Memverifikasi perubahan UI secara dinamis: teks ETA tergantikan oleh
 *              batas waktu ambil saat status berubah ke Siap Diambil.
 * Prasyarat  : Status pesanan sudah "Siap Diambil" dengan pickup_deadline terisi.
 * Expected   : Teks estimasi penyiapan hilang. Muncul batas akhir pengambilan.
 * Hasil      : Pass
 * Tanggal    : 10/06/2026
 */
class TCETA002Test extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer ETA002', 'email' => 'buyer_eta002@test.com',
            'role' => 'buyer', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerUser = User::factory()->create([
            'name' => 'Seller ETA002', 'email' => 'seller_eta002@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id, 'store_name' => 'Toko ETA002',
            'address' => 'Jl. ETA002', 'verification_status' => 'approved',
            'close_time' => '23:00',
        ]);
        $category = Category::firstOrCreate(['name' => 'Makanan']);
        $product = Product::create([
            'seller_id' => $seller->id, 'category_id' => $category->id,
            'name' => 'Menu ETA002', 'base_price' => 20000,
        ]);
        Stock::create(['product_id' => $product->id, 'qty_reg' => 10, 'qty_surplus' => 0]);

        $order = Order::create([
            'buyer_id' => $buyer->id, 'seller_id' => $seller->id,
            'total_amount' => 20000, 'payment_method' => 'cash',
            'status' => 'siap_diambil', 'pickup_deadline' => now()->addHours(2),
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'qty' => 1, 'price' => 20000, 'is_surplus' => false]);

        return compact('buyer', 'order');
    }

    public function test_batas_waktu_ambil_muncul_saat_siap_diambil(): void
    {
        $eco = $this->setupEcosystem();
        $this->browse(function (Browser $browser) use ($eco) {
            $browser->loginAs($eco['buyer'])
                ->visitRoute('buyer.orders.show', $eco['order']->id)
                ->pause(1500)
                ->assertDontSee('Pesanan sedang disiapkan')
                ->assertSee('Harap ambil pesanan')
                ->assertSee('Batas Maksimal')
                ->assertSee('WIB')
                ->screenshot('tc_eta_002_batas_waktu_siap_diambil');
        });
    }

    public function test_warning_batas_waktu_terlewat_muncul(): void
    {
        $eco = $this->setupEcosystem();
        // Simulasikan batas waktu lewat 1 jam yang lalu
        $eco['order']->update(['pickup_deadline' => now()->subHour()]);

        $this->browse(function (Browser $browser) use ($eco) {
            $browser->loginAs($eco['buyer'])
                ->visitRoute('buyer.orders.show', $eco['order']->id)
                ->pause(1500)
                ->assertSee('Pesanan Hangus')
                ->assertSee('tidak diambil dalam batas waktu')
                ->screenshot('tc_eta_002_batas_waktu_terlewat');
        });

        // Verifikasi status berubah ke hangus di database
        $eco['order']->refresh();
        $this->assertEquals('hangus', $eco['order']->status);
    }
}
