<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;

class PBI19ProductReviewTC02Test extends DuskTestCase
{
/**
     * TS.PBI.019 - Validasi Ulasan Ganda (Negative)
     * Mencoba memberikan ulasan kedua kali pada ID yang sama.
     */
    public function test_buyer_cannot_submit_duplicate_review(): void
    {
        $order = $this->createCompletedOrder(true); // Buat order selesai YANG SUDAH diulas
        $buyer = $order->buyer;

        $this->browse(function (Browser $browser) use ($buyer): void {
            $browser->loginAs($buyer)
                ->visit('/buyer/orders?tab=riwayat')
                ->waitForText('ULASAN DIKIRIM', 10) // Teks di-capitalize oleh CSS uppercase
                
                // Pastikan tombol "Beri Ulasan" TIDAK ada
                ->assertDontSee('Beri Ulasan')
                
                // Pastikan yang muncul adalah status "Ulasan Dikirim" (dalam huruf kapital)
                ->assertSee('ULASAN DIKIRIM');
        });
    }
/**
     * Helper: Membuat transaksi selesai
     */
    private function createCompletedOrder(bool $withReview = false): Order
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Kedai Kopi Budi ' . uniqid(),
            'verification_status' => 'approved',
            'address' => 'Jl. Kopi No. 5, Bandung',
            'latitude' => -6.9147,
            'longitude' => 107.6098,
        ]);

        $category = Category::firstOrCreate(['name' => 'Minuman']);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Es Kopi Susu Aren ' . uniqid(),
            'description' => 'Es kopi susu dengan gula aren murni',
            'base_price' => 10000,
            'image' => 'products/dummy.jpg',
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'total_amount' => 10000,
            'payment_method' => 'cash',
            'status' => 'selesai',
            'pickup_code' => 'SISA-' . rand(10000, 99999),
            'pickup_deadline' => now()->addHours(2),
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 10000,
        ]);

        if ($withReview) {
            $order->review()->create([
                'seller_id' => $seller->id,
                'buyer_id' => $buyer->id,
                'rating' => 5,
                'comment' => 'Enak sekali kopi susunya!',
            ]);
        }

        return $order->load('review');
    }
}
