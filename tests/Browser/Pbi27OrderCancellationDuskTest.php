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

class Pbi27OrderCancellationDuskTest extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem($isSurplus = false, $orderAgeSeconds = 0)
    {
        $buyer = User::factory()->create([
            'role' => 'buyer',
            'password' => bcrypt('password'),
        ]);

        $sellerUser = User::factory()->create([
            'role' => 'seller',
            'password' => bcrypt('password'),
        ]);

        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Toko Pbi27',
            'address' => 'Jl. Pbi 27',
            'verification_status' => 'approved',
        ]);

        $category = Category::firstOrCreate(['name' => 'Makanan']);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Produk Pbi27',
            'base_price' => 20000,
            'image' => 'products/dummy.jpg',
        ]);

        $stock = Stock::create([
            'product_id' => $product->id,
            'qty_reg' => 10,
            'qty_surplus' => 5,
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'total_amount' => 40000,
            'payment_method' => 'cash',
            'status' => 'menunggu_verifikasi',
            'created_at' => now()->subSeconds($orderAgeSeconds),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 2,
            'price' => 20000,
            'is_surplus' => $isSurplus,
        ]);

        return compact('buyer', 'sellerUser', 'seller', 'product', 'stock', 'order');
    }

    private function loginAs(Browser $browser, User $user): Browser
    {
        return $browser->loginAs($user);
    }

    public function test_buyer_cancel_surplus_product_within_15_seconds(): void
    {
        // TC-27.1
        $eco = $this->setupEcosystem(true); // is_surplus = true

        $this->browse(function (Browser $browser) use ($eco) {
            $this->loginAs($browser, $eco['buyer'])
                ->visitRoute('buyer.orders.show', $eco['order']->id)
                ->waitForText('Sisa Waktu Pembatalan')
                ->click('#btn-open-cancel-modal')
                ->pause(1000)
                ->select('#cancellation_reason_select', 'Saya berubah pikiran')
                ->press('form[action*="cancel"] button[type="submit"]')
                ->pause(2000)
                ->assertSee('Pesanan Berhasil di batalkan');
        });

        $eco['order']->refresh();
        $this->assertEquals('dibatalkan', $eco['order']->status);

        // Verifikasi pengembalian qty_surplus (5 + 2 = 7)
        $eco['stock']->refresh();
        $this->assertEquals(7, $eco['stock']->qty_surplus);
        $this->assertEquals(10, $eco['stock']->qty_reg);
    }

    public function test_buyer_cancel_regular_product_within_15_seconds(): void
    {
        // TC-27.2
        $eco = $this->setupEcosystem(false); // is_surplus = false

        $this->browse(function (Browser $browser) use ($eco) {
            $this->loginAs($browser, $eco['buyer'])
                ->visitRoute('buyer.orders.show', $eco['order']->id)
                ->waitForText('Sisa Waktu Pembatalan')
                ->click('#btn-open-cancel-modal')
                ->pause(1000)
                ->select('#cancellation_reason_select', 'Saya berubah pikiran')
                ->press('form[action*="cancel"] button[type="submit"]')
                ->pause(2000)
                ->assertSee('Pesanan Berhasil di batalkan');
        });

        $eco['order']->refresh();
        $this->assertEquals('dibatalkan', $eco['order']->status);

        // Verifikasi pengembalian qty_reg (10 + 2 = 12)
        $eco['stock']->refresh();
        $this->assertEquals(5, $eco['stock']->qty_surplus);
        $this->assertEquals(12, $eco['stock']->qty_reg);
    }

    public function test_buyer_cancel_feature_locked_after_15_seconds(): void
    {
        // TC-27.3
        $eco = $this->setupEcosystem(false);

        $this->browse(function (Browser $browser) use ($eco) {
            $this->loginAs($browser, $eco['buyer'])
                ->visitRoute('buyer.orders.show', $eco['order']->id)
                ->waitForText('Sisa Waktu Pembatalan')
                ->assertVisible('#btn-open-cancel-modal')
                // Tunggu maksimal 16 detik sampai tombol hilang karena timer habis
                ->waitUntilMissing('#btn-open-cancel-modal', 20);
        });
    }

    public function test_seller_can_cancel_order_anytime(): void
    {
        // TC-27.4: Buat pesanan sudah 30 detik (jauh melewati batas 15 detik buyer)
        $eco = $this->setupEcosystem(false, 30); 

        $this->browse(function (Browser $browser) use ($eco) {
            $this->loginAs($browser, $eco['sellerUser'])
                ->visitRoute('seller.orders')
                ->waitForText('Tolak Pesanan')
                ->click('.btn-tolak')
                ->pause(1000)
                ->type('#cancellation_reason', 'Stok habis')
                ->press('form#reject-form button[type="submit"]')
                ->pause(2000)
                ->assertSee('telah dibatalkan');
        });

        $eco['order']->refresh();
        $this->assertEquals('dibatalkan', $eco['order']->status);
        $this->assertEquals('Stok habis', $eco['order']->cancellation_reason);
    }
}
