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
 * TC-PAY-003 | PBI 16 - Manajemen Pembayaran
 *
 * Tujuan     : Memverifikasi bahwa sistem menampilkan pesan peringatan dan
 *              menonaktifkan opsi QRIS apabila toko belum mengatur barcode QRIS.
 * Prasyarat  : Toko belum melengkapi profil barcode QRIS.
 * Expected   : Sistem menampilkan "QRIS Belum Tersedia". Tombol "Buat Pesanan" disabled.
 * Hasil      : Pass
 * Tanggal    : 10/06/2026
 */
class TCPAY003Test extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer PAY003', 'email' => 'buyer_pay003@test.com',
            'role' => 'buyer', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerUser = User::factory()->create([
            'name' => 'Seller PAY003', 'email' => 'seller_pay003@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id, 'store_name' => 'Toko Tanpa QRIS',
            'address' => 'Jl. No QRIS', 'verification_status' => 'approved',
            'qris_image' => null,
        ]);
        $category = Category::firstOrCreate(['name' => 'Makanan']);
        $product = Product::create([
            'seller_id' => $seller->id, 'category_id' => $category->id,
            'name' => 'Nasi Goreng PAY003', 'base_price' => 20000,
        ]);
        Stock::create(['product_id' => $product->id, 'qty_reg' => 10, 'qty_surplus' => 0]);
        Cart::create([
            'buyer_id' => $buyer->id, 'product_id' => $product->id,
            'seller_id' => $seller->id, 'qty' => 1, 'is_surplus' => false,
        ]);
        return compact('buyer');
    }

    public function test_toko_tanpa_qris_menampilkan_peringatan_dan_disable_submit(): void
    {
        $eco = $this->setupEcosystem();
        $this->browse(function (Browser $browser) use ($eco) {
            $browser->loginAs($eco['buyer'])
                ->visitRoute('buyer.checkout')->pause(1000)
                ->click('#payment-option-qris')->pause(500)
                ->waitFor('#qris-not-available')
                ->assertSee('QRIS Belum Tersedia')
                ->assertDisabled('#btn-buat-pesanan')
                ->screenshot('tc_pay_003_qris_not_available');
        });
    }
}
