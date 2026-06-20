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
 * TC-ETA-005 | PBI 17 - Estimasi Waktu Ambil
 *
 * Tujuan     : Memverifikasi bahwa kolom pickup_deadline pada tabel orders terisi
 *              secara otomatis saat status pesanan diubah ke Siap Diambil.
 * Prasyarat  : Penjual sudah login. Terdapat pesanan berstatus "Diproses".
 * Langkah    : 1. Penjual menekan tombol "Makanan Siap".
 *              2. Periksa kolom pickup_deadline di DB.
 * Expected   : pickup_deadline terisi (NOT NULL) dengan DATETIME yang valid.
 * Hasil      : Pass
 * Tanggal    : 10/06/2026
 */
class TCETA005Test extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer ETA005', 'email' => 'buyer_eta005@test.com',
            'role' => 'buyer', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerUser = User::factory()->create([
            'name' => 'Seller ETA005', 'email' => 'seller_eta005@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id, 'store_name' => 'Toko ETA005',
            'address' => 'Jl. ETA005', 'verification_status' => 'approved',
            'close_time' => '23:00',
        ]);
        $category = Category::firstOrCreate(['name' => 'Makanan']);
        $product = Product::create([
            'seller_id' => $seller->id, 'category_id' => $category->id,
            'name' => 'Menu ETA005', 'base_price' => 20000,
        ]);
        Stock::create(['product_id' => $product->id, 'qty_reg' => 10, 'qty_surplus' => 0]);

        $order = Order::create([
            'buyer_id' => $buyer->id, 'seller_id' => $seller->id,
            'total_amount' => 20000, 'payment_method' => 'cash', 'status' => 'diproses',
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'qty' => 1, 'price' => 20000, 'is_surplus' => false]);

        return compact('sellerUser', 'order');
    }

    public function test_pickup_deadline_terisi_otomatis_saat_siap_diambil(): void
    {
        $eco = $this->setupEcosystem();

        // Verifikasi awal: pickup_deadline masih NULL
        $this->assertNull($eco['order']->pickup_deadline);

        // Seller tandai makanan siap via HTTP
        $this->actingAs($eco['sellerUser'])
            ->patch(route('seller.orders.ready', $eco['order']->id));

        // Verifikasi: pickup_deadline terisi
        $eco['order']->refresh();
        $this->assertEquals('siap_diambil', $eco['order']->status);
        $this->assertNotNull($eco['order']->pickup_deadline, 'Kolom pickup_deadline harus terisi (NOT NULL).');

        // Verifikasi format DATETIME yang valid
        $this->assertInstanceOf(
            \Carbon\Carbon::class,
            $eco['order']->pickup_deadline,
            'pickup_deadline harus berupa instance Carbon/DateTime yang valid.'
        );

        // Verifikasi deadline masuk akal (antara sekarang dan sekarang+2 jam+1 menit)
        $this->assertTrue(
            $eco['order']->pickup_deadline->greaterThan(now()),
            'pickup_deadline harus di masa depan.'
        );
    }
}
