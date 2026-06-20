<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Seller;
use App\Models\User;
use App\Models\Voucher;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi33VoucherCheckoutDuskTest extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer PbiTigaTiga',
            'email' => 'buyer33@test.com',
            'role' => 'buyer',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $sellerUser = User::factory()->create([
            'name' => 'Seller PbiTigaTiga',
            'email' => 'seller33@test.com',
            'role' => 'seller',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Toko PbiTigaTiga',
            'address' => 'Jl. Pbi Tiga Tiga No. 33',
            'verification_status' => 'approved',
        ]);

        $category = Category::firstOrCreate(['name' => 'Makanan']);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Nasi Goreng Pbi33',
            'base_price' => 20000,
            'image' => 'products/nasgor33.jpg',
        ]);

        $stock = Stock::create([
            'product_id' => $product->id,
            'qty_reg' => 10,
            'qty_surplus' => 5,
        ]);

        $voucher = Voucher::create([
            'seller_id' => $seller->id,
            'code' => 'DISKON10',
            'type' => 'percent',
            'value' => 10,
            'min_order' => 15000,
            'is_active' => true,
        ]);

        return compact('buyer', 'sellerUser', 'seller', 'product', 'stock', 'voucher');
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

    public function test_buyer_can_apply_voucher_at_checkout(): void
    {
        $eco = $this->setupEcosystem();

        Cart::create([
            'buyer_id' => $eco['buyer']->id,
            'product_id' => $eco['product']->id,
            'qty' => 2, // Total: 40000 (melebihi min_order 15000)
            'is_surplus' => false,
        ]);

        $this->browse(function (Browser $browser) use ($eco) {
            $this->loginAs($browser, $eco['buyer'])
                ->visitRoute('buyer.checkout')
                ->waitForText('Ringkasan Pesanan')
                // Tipe kode promo
                ->type('#promo_code_input', 'DISKON10')
                // Klik gunakan
                ->press('Gunakan')
                // Tunggu respons AJAX
                ->waitForText('Voucher berhasil digunakan!')
                // Verifikasi total berubah (Diskon 10% dari 40.000 = 4.000, total baru = 36.000)
                ->assertSee('Potongan Voucher')
                ->assertSee('Rp 36.000')
                // Selesaikan checkout
                ->radio('payment_method', 'cash')
                ->click('#btn-buat-pesanan')
                ->waitForText('Menunggu Verifikasi')
                ->assertSee('Rp 36.000');
        });

        // Verifikasi database bahwa order menyimpan detail voucher
        $order = Order::where('buyer_id', $eco['buyer']->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('DISKON10', $order->voucher_code);
        $this->assertEquals(4000, $order->discount_amount);
        $this->assertEquals(36000, $order->total_amount);
    }
}
