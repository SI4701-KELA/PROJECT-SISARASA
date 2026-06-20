<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Seller;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi7StockSyncDuskTest extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer PbiTujuh',
            'email' => 'buyer7@test.com',
            'role' => 'buyer',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $sellerUser = User::factory()->create([
            'name' => 'Seller PbiTujuh',
            'email' => 'seller7@test.com',
            'role' => 'seller',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Toko PbiTujuh',
            'address' => 'Jl. Pbi Tujuh No. 7',
            'verification_status' => 'approved',
        ]);

        $category = Category::firstOrCreate(['name' => 'Makanan']);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Nasi Goreng Pbi7',
            'base_price' => 20000,
            'image' => 'products/nasgor7.jpg',
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

    public function test_stock_decreased_after_checkout(): void
    {
        $eco = $this->setupEcosystem();

        Cart::create([
            'buyer_id' => $eco['buyer']->id,
            'product_id' => $eco['product']->id,
            'qty' => 3,
            'is_surplus' => false,
        ]);

        $this->browse(function (Browser $browser) use ($eco) {
            $this->loginAs($browser, $eco['buyer'])
                ->visitRoute('buyer.checkout')
                ->waitForText('Ringkasan Pesanan')
                ->click('#payment-option-cash')
                ->pause(500)
                ->press('#btn-buat-pesanan')
                ->waitForText('Menunggu Verifikasi')
                ->assertSee('sedang diverifikasi oleh toko.');
        });

        // Verifikasi database bahwa stok berkurang dari 10 menjadi 7
        $eco['stock']->refresh();
        $this->assertEquals(7, $eco['stock']->qty_reg);
    }
}
