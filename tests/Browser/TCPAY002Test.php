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
 * TC-PAY-002 | PBI 16 - Manajemen Pembayaran
 *
 * Tujuan     : Memverifikasi bahwa barcode QRIS yang ditampilkan bersifat dinamis
 *              dan sesuai dengan toko penjual yang sedang di-checkout.
 * Prasyarat  : Keranjang berisi produk dari Toko A dan Toko B bergantian.
 * Langkah    : 1. Masukkan produk Toko A, buka Checkout, pilih QRIS, catat barcode.
 *              2. Ganti produk Toko B, ulangi.
 * Expected   : Barcode QRIS Toko A berbeda dengan Toko B sesuai seller_id.
 * Hasil      : Pass
 * Tanggal    : 10/06/2026
 */
class TCPAY002Test extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer PAY002', 'email' => 'buyer_pay002@test.com',
            'role' => 'buyer', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $category = Category::firstOrCreate(['name' => 'Makanan']);

        // Toko A
        $sellerUserA = User::factory()->create([
            'name' => 'Seller A', 'email' => 'seller_a_pay002@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerA = Seller::create([
            'user_id' => $sellerUserA->id, 'store_name' => 'Toko A PAY002',
            'address' => 'Jl. A', 'verification_status' => 'approved',
            'qris_image' => 'qris_images/toko_a.png',
        ]);
        $productA = Product::create([
            'seller_id' => $sellerA->id, 'category_id' => $category->id,
            'name' => 'Menu Toko A', 'base_price' => 20000,
        ]);
        Stock::create(['product_id' => $productA->id, 'qty_reg' => 10, 'qty_surplus' => 0]);

        // Toko B
        $sellerUserB = User::factory()->create([
            'name' => 'Seller B', 'email' => 'seller_b_pay002@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerB = Seller::create([
            'user_id' => $sellerUserB->id, 'store_name' => 'Toko B PAY002',
            'address' => 'Jl. B', 'verification_status' => 'approved',
            'qris_image' => 'qris_images/toko_b.png',
        ]);
        $productB = Product::create([
            'seller_id' => $sellerB->id, 'category_id' => $category->id,
            'name' => 'Menu Toko B', 'base_price' => 25000,
        ]);
        Stock::create(['product_id' => $productB->id, 'qty_reg' => 10, 'qty_surplus' => 0]);

        return compact('buyer', 'sellerA', 'sellerB', 'productA', 'productB');
    }

    public function test_barcode_qris_dinamis_sesuai_toko(): void
    {
        $eco = $this->setupEcosystem();
        $this->browse(function (Browser $browser) use ($eco) {
            // Checkout Toko A
            Cart::query()->delete();
            Cart::create([
                'buyer_id' => $eco['buyer']->id, 'product_id' => $eco['productA']->id,
                'seller_id' => $eco['sellerA']->id, 'qty' => 1, 'is_surplus' => false,
            ]);
            $browser->loginAs($eco['buyer'])
                ->visitRoute('buyer.checkout')->pause(1000)
                ->click('#payment-option-qris')->pause(500)
                ->assertSourceHas('toko_a.png')
                ->screenshot('tc_pay_002_qris_toko_a');

            // Checkout Toko B
            Cart::query()->delete();
            Cart::create([
                'buyer_id' => $eco['buyer']->id, 'product_id' => $eco['productB']->id,
                'seller_id' => $eco['sellerB']->id, 'qty' => 1, 'is_surplus' => false,
            ]);
            $browser->visitRoute('buyer.checkout')->pause(1000)
                ->click('#payment-option-qris')->pause(500)
                ->assertSourceHas('toko_b.png')
                ->assertSourceMissing('toko_a.png')
                ->screenshot('tc_pay_002_qris_toko_b');
        });
    }
}
