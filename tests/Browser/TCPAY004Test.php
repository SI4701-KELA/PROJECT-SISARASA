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
 * TC-PAY-004 | PBI 16 - Manajemen Pembayaran
 *
 * Tujuan     : Memverifikasi alur pembuatan pesanan menggunakan metode pembayaran
 *              QRIS berjalan lancar beserta unggah bukti transfer.
 * Prasyarat  : Pembeli sudah login dan berada di halaman Checkout dengan item di keranjang.
 * Langkah    : 1. Pilih opsi QRIS. 2. Upload bukti transfer. 3. Klik "Buat Pesanan".
 * Expected   : Pesanan berhasil dibuat dengan status "Menunggu Verifikasi".
 * Hasil      : Pass
 * Tanggal    : 10/06/2026
 */
class TCPAY004Test extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer PAY004', 'email' => 'buyer_pay004@test.com',
            'role' => 'buyer', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerUser = User::factory()->create([
            'name' => 'Seller PAY004', 'email' => 'seller_pay004@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id, 'store_name' => 'Toko PAY004',
            'address' => 'Jl. PAY004', 'verification_status' => 'approved',
            'qris_image' => 'qris_images/pay004.png',
        ]);
        $category = Category::firstOrCreate(['name' => 'Makanan']);
        $product = Product::create([
            'seller_id' => $seller->id, 'category_id' => $category->id,
            'name' => 'Nasi Goreng PAY004', 'base_price' => 20000,
        ]);
        Stock::create(['product_id' => $product->id, 'qty_reg' => 10, 'qty_surplus' => 0]);
        Cart::create([
            'buyer_id' => $buyer->id, 'product_id' => $product->id,
            'seller_id' => $seller->id, 'qty' => 1, 'is_surplus' => false,
        ]);
        return compact('buyer');
    }

    public function test_alur_checkout_qris_berhasil(): void
    {
        $eco = $this->setupEcosystem();
        $this->browse(function (Browser $browser) use ($eco) {
            $dummyImage = base_path('tests/Browser/photos/dummy-bukti.png');

            $browser->loginAs($eco['buyer'])
                ->visitRoute('buyer.checkout')->pause(1000)
                ->click('#payment-option-qris')->pause(500)
                ->attach('input[name="payment_proof"]', $dummyImage)->pause(500)
                ->click('#btn-buat-pesanan')
                ->pause(2000)
                ->assertPathBeginsWith('/buyer/checkout/success/')
                ->assertSee('Menunggu Verifikasi')
                ->screenshot('tc_pay_004_checkout_qris_success');
        });
    }
}
