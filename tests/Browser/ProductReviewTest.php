<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;

class ProductReviewTest extends DuskTestCase
{
    /**
     * TS.PBI.019 - Input Ulasan & Rating (Positive)
     * Memberikan ulasan teks dan rating bintang 1-5.
     */
    public function test_buyer_submits_review_successfully(): void
    {
        $order = $this->createCompletedOrder(false); // Buat order selesai tanpa ulasan
        $buyer = $order->buyer;

        $this->browse(function (Browser $browser) use ($buyer): void {
            $browser->loginAs($buyer)
                ->visit('/buyer/orders?tab=riwayat')
                ->waitForText('Beri Ulasan', 10)
                ->press('Beri Ulasan') // Klik tombol Beri Ulasan
                ->waitFor('.fixed[role="dialog"]', 5) // Tunggu modal ulasan muncul
                
                // Tunggu tombol rating bintang ke-5 ter-render oleh Alpine.js
                ->waitFor('#star-rating-5', 5)
                
                // Pilih Rating Bintang 5
                ->click('#star-rating-5')
                
                // Isi Ulasan Tertulis
                ->type('#comment', 'Makanannya sangat lezat dan pelayanannya cepat!')
                
                // Klik Kirim Ulasan
                ->press('Kirim Ulasan')
                
                // Tunggu dan pastikan toast sukses muncul
                ->waitForText('Feedback-mu sangat berharga', 10);
        });
    }

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
            'pickup_code' => 'SISA-55555',
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
