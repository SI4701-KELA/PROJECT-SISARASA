<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Stock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * TC-ETA-004 | PBI 17 - Estimasi Waktu Ambil
 *
 * Tujuan     : Memverifikasi bahwa sistem memprioritaskan jam tutup toko sebagai
 *              batas waktu ambil apabila kalkulasi +2 jam melampaui jam operasional.
 * Prasyarat  : Jam tutup toko 21:00 WIB. Pesanan ditandai "Siap Diambil" pada 20:00 WIB.
 * Expected   : pickup_deadline = 21:00 WIB (jam tutup toko, bukan 22:00 WIB).
 * Hasil      : Fail (sebelum fix timezone) → Pass (setelah fix timezone)
 * Tanggal    : 10/06/2026
 *
 * Catatan    : Test ini memvalidasi bug fix timezone di SellerOrderController::markReady().
 *              Sebelumnya close_time (WIB) dibandingkan dengan now() (UTC) sehingga
 *              kalkulasi salah. Fix: gunakan now()->timezone('Asia/Jakarta').
 */
class TCETA004Test extends DuskTestCase
{
    use DatabaseTruncation, WithoutMiddleware;

    private function setupEcosystem()
    {
        $buyer = User::factory()->create([
            'name' => 'Buyer ETA004', 'email' => 'buyer_eta004@test.com',
            'role' => 'buyer', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);
        $sellerUser = User::factory()->create([
            'name' => 'Seller ETA004', 'email' => 'seller_eta004@test.com',
            'role' => 'seller', 'password' => bcrypt('password'), 'email_verified_at' => now(),
        ]);

        // Jam tutup toko = 1 jam dari sekarang (WIB), sehingga +2 jam akan melampaui
        $nowWib = now()->timezone('Asia/Jakarta');
        $closeTime = $nowWib->copy()->addHour()->format('H:i');

        $seller = Seller::create([
            'user_id' => $sellerUser->id, 'store_name' => 'Toko ETA004',
            'address' => 'Jl. ETA004', 'verification_status' => 'approved',
            'close_time' => $closeTime,
        ]);
        $category = Category::firstOrCreate(['name' => 'Makanan']);
        $product = Product::create([
            'seller_id' => $seller->id, 'category_id' => $category->id,
            'name' => 'Menu ETA004', 'base_price' => 20000,
        ]);
        Stock::create(['product_id' => $product->id, 'qty_reg' => 10, 'qty_surplus' => 0]);

        $order = Order::create([
            'buyer_id' => $buyer->id, 'seller_id' => $seller->id,
            'total_amount' => 20000, 'payment_method' => 'cash', 'status' => 'diproses',
        ]);
        OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'qty' => 1, 'price' => 20000, 'is_surplus' => false]);

        return compact('buyer', 'sellerUser', 'seller', 'order', 'closeTime');
    }

    public function test_prioritas_jam_tutup_toko_sebagai_batas_waktu_ambil(): void
    {
        $eco = $this->setupEcosystem();

        // Seller tandai makanan siap
        $this->actingAs($eco['sellerUser'])
            ->patch(route('seller.orders.ready', $eco['order']->id));

        // Verifikasi: pickup_deadline harus = close_time, BUKAN now + 2 jam
        $eco['order']->refresh();
        $this->assertEquals('siap_diambil', $eco['order']->status);
        $this->assertNotNull($eco['order']->pickup_deadline);

        $deadlineWib = $eco['order']->pickup_deadline->timezone('Asia/Jakarta');
        $expectedCloseTime = $eco['closeTime']; // format H:i

        $this->assertEquals(
            $expectedCloseTime,
            $deadlineWib->format('H:i'),
            "pickup_deadline seharusnya = jam tutup toko ({$expectedCloseTime} WIB), bukan now+2h. Got: " . $deadlineWib->format('H:i')
        );
    }
}
