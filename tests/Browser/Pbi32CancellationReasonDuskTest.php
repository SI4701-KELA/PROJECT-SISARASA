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

class Pbi32CancellationReasonDuskTest extends DuskTestCase
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

    public function test_buyer_can_cancel_order_with_dropdown_reason(): void
    {
        $eco = $this->setupEcosystem();

        $this->browse(function (Browser $browser) use ($eco) {
            $this->loginAs($browser, $eco['buyer'])
                ->visitRoute('buyer.orders.show', $eco['order']->id)
                ->waitForText('Metode Pembayaran')
                // Scroll ke tombol dan klik tombol "Batalkan Pesanan" utama
                ->scrollIntoView('#btn-open-cancel-modal')
                ->pause(500)
                ->click('#btn-open-cancel-modal')
                ->pause(1000) // Tunggu modal muncul
                // Pilih alasan di dropdown
                ->select('#cancellation_reason_select', 'Saya berubah pikiran')
                ->pause(500)
                // Klik tombol submit di dalam form modal
                ->press('form[action*="cancel"] button[type="submit"]')
                ->pause(2000) // Tunggu proses selesai
                ->assertSee('Pesanan Berhasil di batalkan');
        });

        // Verifikasi database bahwa status pesanan dibatalkan dengan alasan yang dipilih
        $eco['order']->refresh();
        $this->assertEquals('dibatalkan', $eco['order']->status);
        $this->assertEquals('Saya berubah pikiran', $eco['order']->cancellation_reason);

        // Verifikasi stok dikembalikan dari 10 ke 12 (karena pesanan dibatalkan)
        $eco['stock']->refresh();
        $this->assertEquals(12, $eco['stock']->qty_reg);
    }
}
