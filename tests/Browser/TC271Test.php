<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Seller;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Discount;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC271Test extends DuskTestCase
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
            'qty_reg' => 10,
            'qty_surplus' => 5,
        ]);

        Discount::create([
            'product_id' => $product->id,
            'discount_price' => 15000,
            'is_active' => true,
        ]);

        // Buat pesanan baru berstatus menunggu_verifikasi agar timer 15 detik aktif
        $order = Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'total_amount' => 40000,
            'payment_method' => 'cash',
            'status' => 'menunggu_verifikasi',
            'created_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 2,
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


    public function test_batal_pesanan_promo_mengembalikan_stok_surplus(): void
    {
        $eco = $this->setupEcosystem();

        $this->browse(function (Browser $browser) use ($eco) {
            $this->loginAs($browser, $eco['buyer'])
                ->visit('/buyer/menu')
                ->waitForText('Nasi Goreng Pbi32')
                ->assertSee('PROMO') // Memastikan label PROMO terlihat
                // Klik tombol [+] untuk memesan 1 porsi
                ->click('.flex-1.bg-white button:nth-child(3)')
                ->pause(500)
                // Klik tombol "+" / Tambah ke keranjang
                ->waitFor('button[x-show="qty > 0"]')
                ->click('button[x-show="qty > 0"]')
                ->pause(2000)
                // Pergi ke checkout
                ->visitRoute('buyer.checkout')
                ->waitForText('Ringkasan Pesanan')
                ->radio('payment_method', 'cash')
                ->click('#btn-buat-pesanan')
                // Menunggu halaman sukses pemesanan
                ->waitForText('Menunggu Verifikasi')
                ->assertSee('sedang diverifikasi oleh toko.')
                // Klik tombol Batalkan Pesanan di halaman sukses
                ->press('Batalkan Pesanan')
                ->pause(1000)
                ->select('#cancellation_reason_select', 'Saya berubah pikiran')
                ->pause(500)
                ->press('form[action*="cancel"] button[type="submit"]')
                ->pause(2000)
                ->assertSee('Pesanan Dibatalkan');
        });

        // Verifikasi database bahwa status pesanan dibatalkan
        $order = Order::where('buyer_id', $eco['buyer']->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('dibatalkan', $order->status);
        $this->assertEquals('Saya berubah pikiran', $order->cancellation_reason);

        // Verifikasi stok surplus dikembalikan ke 5 (awalnya 5, dipotong 1 saat checkout menjadi 4, lalu dikembalikan 1 menjadi 5)
        $eco['stock']->refresh();
        $this->assertEquals(5, $eco['stock']->qty_surplus);
        $this->assertEquals(10, $eco['stock']->qty_reg); // qty_reg tetap 10 karena pesanan surplus
    }
}
