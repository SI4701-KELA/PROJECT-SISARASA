<?php

namespace Tests\Browser;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC-PAY-001 | PBI 16 - Manajemen Pembayaran
 *
 * Tujuan     : Memverifikasi bahwa halaman Checkout hanya menyediakan dua opsi
 *              metode pembayaran yang tersedia, yaitu Cash dan QRIS.
 * Prasyarat  : Pembeli sudah login dan berada di halaman Checkout.
 * Langkah    : 1. Buka halaman Checkout.
 *              2. Periksa area pemilihan metode pembayaran.
 * Expected   : Halaman Checkout hanya menampilkan dua opsi radio button secara
 *              eksplisit: "Cash" dan "QRIS". Tidak ada opsi lain di luar keduanya.
 * Hasil      : Pass
 * Tanggal    : 10/06/2026
 */
class TCPAY001Test extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer PAY001', 'email' => 'buyer_pay001@test.com',
            'role' => 'buyer', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerUser = User::factory()->create([
            'name' => 'Seller PAY001', 'email' => 'seller_pay001@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id, 'store_name' => 'Toko PAY001',
            'address' => 'Jl. PAY001', 'verification_status' => 'approved',
            'qris_image' => 'qris_images/pay001.png',
        ]);
        $category = Category::firstOrCreate(['name' => 'Makanan']);
        $product = Product::create([
            'seller_id' => $seller->id, 'category_id' => $category->id,
            'name' => 'Nasi Goreng PAY001', 'base_price' => 20000,
        ]);
        Stock::create(['product_id' => $product->id, 'qty_reg' => 10, 'qty_surplus' => 0]);
        Cart::create([
            'buyer_id' => $buyer->id, 'product_id' => $product->id,
            'seller_id' => $seller->id, 'qty' => 1, 'is_surplus' => false,
        ]);
        return compact('buyer');
    }

    public function test_checkout_hanya_menampilkan_dua_opsi_cash_dan_qris(): void
    {
        $eco = $this->setupEcosystem();
        $this->browse(function (Browser $browser) use ($eco) {
            $browser->loginAs($eco['buyer'])
                ->visitRoute('buyer.checkout')
                ->pause(1000)
                ->assertSee('Cash')
                ->assertSee('QRIS');

            // Pastikan hanya ada 2 radio button payment_method
            $allRadios = $browser->driver->findElements(
                \Facebook\WebDriver\WebDriverBy::cssSelector('input[name="payment_method"]')
            );
            $this->assertCount(2, $allRadios, 'Jumlah opsi metode pembayaran bukan 2.');

            $browser->screenshot('tc_pay_001_checkout_payment_options');
        });
    }
}
