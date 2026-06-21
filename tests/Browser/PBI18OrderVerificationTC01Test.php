<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;

class PBI18OrderVerificationTC01Test extends DuskTestCase
{
/**
     * TS.PBI.018 - Generator QR Code (Positive)
     * Menampilkan QR Code pada pesanan siap ambil.
     */
    public function test_qr_code_generator_displays_correctly(): void
    {
        $order = $this->createOrderWithStatus('siap_diambil', 'SISA-' . rand(10000, 99999));
        $buyer = $order->buyer;

        $this->browse(function (Browser $browser) use ($buyer, $order): void {
            $browser->loginAs($buyer)
                ->visit("/buyer/orders/{$order->id}") // Halaman pelacakan pesanan buyer
                ->pause(2000)
                ->waitFor('#qrcode-canvas', 10)
                ->pause(2000)
                ->assertPresent('#qrcode-canvas') // QR Code Canvas muncul
                ->pause(2000)
                ->assertSee($order->pickup_code); // Kode unik teks muncul
        });
    }
    /**
     * Helper: Membuat data order berdasarkan status dan pickup code
     */
    private function createOrderWithStatus(string $status, string $pickupCode): Order
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $sellerUser = User::factory()->create(['role' => 'seller']);

        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Warung Nasi Budi ' . uniqid(),
            'verification_status' => 'approved',
            'address' => 'Jl. Sudirman No. 12, Bandung',
            'latitude' => -6.9147,
            'longitude' => 107.6098,
        ]);

        $category = Category::firstOrCreate(['name' => 'Makanan Berat']);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Nasi Goreng Rendang ' . uniqid(),
            'description' => 'Nasi goreng lezat dengan bumbu rendang asli',
            'base_price' => 15000,
            'image' => 'products/dummy.jpg',
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'total_amount' => 15000,
            'payment_method' => 'cash',
            'status' => $status,
            'pickup_code' => $pickupCode,
            'pickup_deadline' => now()->addHours(2),
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 15000,
        ]);

        return $order;
    }
}
