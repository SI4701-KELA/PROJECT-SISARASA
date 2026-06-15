<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Seller;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC274Test extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer PbiTigaDua',
            'email' => 'buyer32@test.com',
            'role' => 'buyer',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $sellerUser = User::factory()->create([
            'name' => 'Seller PbiTigaDua',
            'email' => 'seller32@test.com',
            'role' => 'seller',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Toko PbiTigaDua',
            'address' => 'Jl. Pbi Tiga Dua No. 32',
            'verification_status' => 'approved',
        ]);

        $category = Category::firstOrCreate(['name' => 'Makanan']);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Nasi Goreng Pbi32',
            'base_price' => 20000,
            'image' => 'products/nasgor32.jpg',
        ]);

        $stock = Stock::create([
            'product_id' => $product->id,
            'qty_reg' => 9, // Di-set 9 karena checkout memotong 1 porsi (semula 10)
            'qty_surplus' => 5,
        ]);

        // Buat pesanan reguler berstatus menunggu_verifikasi dengan waktu terbuat 30 detik yang lalu (>15 detik)
        $order = Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'total_amount' => 20000,
            'payment_method' => 'cash',
            'status' => 'menunggu_verifikasi',
            'created_at' => now()->subSeconds(30),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 20000,
            'is_surplus' => false,
        ]);

        return compact('buyer', 'sellerUser', 'seller', 'product', 'stock', 'order');
    }

    private function loginAs(Browser $browser, User $user): Browser
    {
        return $browser
            ->visit('/login')
            ->waitFor('#email')
            ->type('#email', $user->email)
            ->type('#password', 'password')
            ->press('Login')
            ->waitUntilMissing('#email', 10);
    }

    public function test_seller_can_cancel_order_after_buyer_time_limit_expires(): void
    {
        $eco = $this->setupEcosystem();

        $this->browse(function (Browser $browser) use ($eco) {
            // 1. Login sebagai Seller
            $this->loginAs($browser, $eco['sellerUser'])
                // 2. Buka daftar pesanan tab "baru" (menunggu_verifikasi)
                ->visitRoute('seller.orders', ['tab' => 'baru'])
                ->waitForText('#' . $eco['order']->id)
                // 3. Seller menekan tombol "Tolak Pesanan"
                ->press('Tolak Pesanan')
                ->waitFor('#reject-modal')
                // 4. Seller mengisi alasan pembatalan "Stok habis"
                ->select('#cancellation_reason', 'Stok habis')
                ->pause(500)
                // 5. Seller mengonfirmasi penolakan/pembatalan
                ->press('Tolak & Batalkan Pesanan')
                ->waitForText('Pembayaran ditolak')
                
                // 6. Login kembali sebagai Buyer
                ->loginAs($eco['buyer'])
                // 7. Buka halaman detail pesanan milik Buyer
                ->visitRoute('buyer.orders.show', $eco['order']->id)
                ->waitForText('Pesanan Berhasil di batalkan')
                // 8. Buyer melihat informasi pembatalan dan alasan yang diisi oleh Seller
                ->assertSee('Pesanan Berhasil di batalkan')
                ->assertSee('ALASAN PEMBATALAN:')
                ->assertSee('Stok habis');
        });

        // 9. Verifikasi status pesanan berubah menjadi dibatalkan dengan alasan di database
        $eco['order']->refresh();
        $this->assertEquals('dibatalkan', $eco['order']->status);
        $this->assertEquals('Stok habis', $eco['order']->cancellation_reason);

        // 10. Verifikasi stok dikembalikan semula menjadi 10
        $eco['stock']->refresh();
        $this->assertEquals(10, $eco['stock']->qty_reg);
        $this->assertEquals(5, $eco['stock']->qty_surplus); // qty_surplus tetap 5
    }
}
