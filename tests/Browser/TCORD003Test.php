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
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC-ORD-003 | PBI 26 - Manajemen Pesanan Masuk (Seller)
 *
 * Tujuan     : Memverifikasi tombol "Lihat Bukti Transfer" tersedia pada pesanan QRIS
 *              dan gambar resi dapat ditampilkan dengan jelas.
 * Prasyarat  : Terdapat pesanan QRIS di Tab Pesanan Baru.
 * Expected   : Modal muncul dan menampilkan foto bukti transfer yang diunggah pembeli.
 * Hasil      : Pass
 * Tanggal    : 10/06/2026
 */
class TCORD003Test extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer ORD003', 'email' => 'buyer_ord003@test.com',
            'role' => 'buyer', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerUser = User::factory()->create([
            'name' => 'Seller ORD003', 'email' => 'seller_ord003@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id, 'store_name' => 'Toko ORD003',
            'address' => 'Jl. ORD003', 'verification_status' => 'approved',
            'qris_image' => 'qris_images/ord003.png',
        ]);
        $category = Category::firstOrCreate(['name' => 'Makanan']);
        $product = Product::create([
            'seller_id' => $seller->id, 'category_id' => $category->id,
            'name' => 'Menu ORD003', 'base_price' => 20000,
        ]);
        Stock::create(['product_id' => $product->id, 'qty_reg' => 10, 'qty_surplus' => 0]);

        // Buat dummy payment proof di storage public
        $paymentProofPath = 'payments/bukti_ord003.jpg';
        Storage::disk('public')->put($paymentProofPath, file_get_contents(base_path('tests/Browser/photos/dummy-bukti.png')));

        $order = Order::create([
            'buyer_id' => $buyer->id, 'seller_id' => $seller->id,
            'total_amount' => 20000, 'payment_method' => 'qris',
            'payment_proof' => $paymentProofPath, 'status' => 'menunggu_verifikasi',
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'qty' => 1, 'price' => 20000, 'is_surplus' => false]);

        return compact('sellerUser', 'order');
    }

    public function test_lihat_bukti_transfer_menampilkan_gambar_di_modal(): void
    {
        $eco = $this->setupEcosystem();
        $this->browse(function (Browser $browser) use ($eco) {
            $browser->loginAs($eco['sellerUser'])
                ->visitRoute('seller.orders', ['tab' => 'baru'])->pause(1000)
                ->assertSee('QRIS')
                ->click('.btn-lihat-bukti')
                ->waitFor('#proof-modal')->pause(500)
                ->assertSee('Bukti Transfer')
                ->waitFor('#proof-image')
                ->screenshot('tc_ord_003_lihat_bukti_transfer');

            $imgSrc = $browser->attribute('#proof-image', 'src');
            $this->assertNotEmpty($imgSrc, 'Gambar bukti transfer tidak memiliki src.');
            $this->assertStringContainsString('bukti_ord003', $imgSrc);
        });
    }
}
