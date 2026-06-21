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
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC-ETA-003 | PBI 17 - Estimasi Waktu Ambil
 *
 * Tujuan     : Memverifikasi kalkulasi batas pengambilan (+2 jam) saat jam tutup toko masih jauh.
 * Prasyarat  : Jam tutup toko 23:00 WIB. Pesanan ditandai "Siap Diambil" saat ini.
 * Expected   : pickup_deadline = now + 2 jam (bukan jam tutup karena masih jauh).
 * Hasil      : Pass
 * Tanggal    : 10/06/2026
 */
class TCETA003Test extends DuskTestCase
{
    use DatabaseTruncation, WithoutMiddleware;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer ETA003', 'email' => 'buyer_eta003@test.com',
            'role' => 'buyer', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerUser = User::factory()->create([
            'name' => 'Seller ETA003', 'email' => 'seller_eta003@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id, 'store_name' => 'Toko ETA003',
            'address' => 'Jl. ETA003', 'verification_status' => 'approved',
            'close_time' => '23:00',
        ]);
        $category = Category::firstOrCreate(['name' => 'Makanan']);
        $product = Product::create([
            'seller_id' => $seller->id, 'category_id' => $category->id,
            'name' => 'Menu ETA003', 'base_price' => 20000,
        ]);
        Stock::create(['product_id' => $product->id, 'qty_reg' => 10, 'qty_surplus' => 0]);

        $order = Order::create([
            'buyer_id' => $buyer->id, 'seller_id' => $seller->id,
            'total_amount' => 20000, 'payment_method' => 'cash', 'status' => 'diproses',
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'qty' => 1, 'price' => 20000, 'is_surplus' => false]);

        return compact('buyer', 'sellerUser', 'order');
    }

    public function test_kalkulasi_batas_ambil_plus_2_jam_saat_tutup_jauh(): void
    {
        $eco = $this->setupEcosystem();

        // Seller tandai makanan siap via HTTP (bukan browser) agar cepat
        $this->actingAs($eco['sellerUser'])
            ->patch(route('seller.orders.ready', $eco['order']->id));

        // Verifikasi di database: pickup_deadline ≈ now + 2 jam
        $eco['order']->refresh();
        $this->assertEquals('siap_diambil', $eco['order']->status);
        $this->assertNotNull($eco['order']->pickup_deadline);

        $expectedDeadline = now()->addHours(2);
        $this->assertTrue(
            abs($eco['order']->pickup_deadline->diffInMinutes($expectedDeadline)) <= 2,
            'pickup_deadline seharusnya ≈ now + 2 jam.'
        );

        // Verifikasi di UI
        $this->browse(function (Browser $browser) use ($eco) {
            $browser->loginAs($eco['buyer'])
                ->visitRoute('buyer.orders.show', $eco['order']->id)
                ->pause(1500)
                ->assertSee('Harap ambil pesanan')
                ->assertSee('Batas Maksimal')
                ->screenshot('tc_eta_003_deadline_plus_2_jam');
        });
    }
}
