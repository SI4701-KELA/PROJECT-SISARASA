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

class TC273Test extends DuskTestCase
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

        return compact('buyer', 'sellerUser', 'seller', 'product', 'stock');
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

    public function test_buyer_cannot_cancel_order_after_15_seconds(): void
    {
        $eco = $this->setupEcosystem();

        $this->browse(function (Browser $browser) use ($eco) {
            $browser->loginAs($eco['buyer'])
                ->visit('/buyer/menu')
                ->waitForText('Nasi Goreng Pbi32')
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
                // Memastikan tombol pembatalan dan timer ada di awal
                ->assertSee('Batalkan Pesanan')
                ->assertSee('Sisa Waktu Pembatalan')
                // Tunggu selama 16 detik (batas waktu pembatalan adalah 15 detik)
                ->pause(20000)
                // Memastikan tombol pembatalan dan komponen timer menghilang setelah batas waktu habis
                ->assertDontSee('Batalkan Pesanan')
                ->assertDontSee('Sisa Waktu Pembatalan');
        });
    }
}
