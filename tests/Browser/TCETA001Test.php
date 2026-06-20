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
 * TC-ETA-001 | PBI 17 - Estimasi Waktu Ambil
 *
 * Tujuan     : Memverifikasi bahwa teks estimasi waktu penyiapan standar ditampilkan
 *              saat pesanan berstatus Diproses.
 * Prasyarat  : Pembeli memiliki pesanan berstatus "Diproses".
 * Expected   : Tampil teks: "Pesanan sedang disiapkan. Estimasi waktu penyiapan: 15-20 Menit".
 * Hasil      : Pass
 * Tanggal    : 10/06/2026
 */
class TCETA001Test extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer ETA001', 'email' => 'buyer_eta001@test.com',
            'role' => 'buyer', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerUser = User::factory()->create([
            'name' => 'Seller ETA001', 'email' => 'seller_eta001@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id, 'store_name' => 'Toko ETA001',
            'address' => 'Jl. ETA001', 'verification_status' => 'approved',
        ]);
        $category = Category::firstOrCreate(['name' => 'Makanan']);
        $product = Product::create([
            'seller_id' => $seller->id, 'category_id' => $category->id,
            'name' => 'Menu ETA001', 'base_price' => 20000,
        ]);
        Stock::create(['product_id' => $product->id, 'qty_reg' => 10, 'qty_surplus' => 0]);

        $order = Order::create([
            'buyer_id' => $buyer->id, 'seller_id' => $seller->id,
            'total_amount' => 20000, 'payment_method' => 'cash', 'status' => 'diproses',
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'qty' => 1, 'price' => 20000, 'is_surplus' => false]);

        return compact('buyer', 'order');
    }

    public function test_estimasi_waktu_penyiapan_ditampilkan_saat_diproses(): void
    {
        $eco = $this->setupEcosystem();
        $this->browse(function (Browser $browser) use ($eco) {
            $browser->loginAs($eco['buyer'])
                ->visitRoute('buyer.orders.show', $eco['order']->id)
                ->pause(1000)
                ->assertSee('Pesanan sedang disiapkan')
                ->assertSee('15-20 Menit')
                ->screenshot('tc_eta_001_estimasi_diproses');
        });
    }
}
