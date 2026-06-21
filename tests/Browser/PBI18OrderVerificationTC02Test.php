<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;

class PBI18OrderVerificationTC02Test extends DuskTestCase
{
/**
     * TS.PBI.018 - Verifikasi Penjual (Positive)
     * Seller melakukan verifikasi penyerahan makanan dengan kode benar.
     */
    public function test_seller_verifies_order_successfully(): void
    {
        $pickupCode = 'SISA-' . rand(100000, 999999); $order = $this->createOrderWithStatus('siap_diambil', $pickupCode);
        $sellerUser = User::find($order->seller->user_id);

        $this->browse(function (Browser $browser) use ($sellerUser, $order, $pickupCode): void {
            $browser->loginAs($sellerUser)
                ->visit('/seller/orders?tab=siap') // Tab siap diambil
                ->waitFor('.btn-verifikasi-pengambilan', 10)
                ->pause(2000)
                ->click('.btn-verifikasi-pengambilan') // Klik tombol verifikasi
                ->pause(2000)
                ->waitFor('#verify-modal', 5)
                ->pause(2000)
                
                // Klik tab input manual
                ->press('Input Manual')
                ->pause(2000)
                ->waitFor('#pickup_code_input', 5)
                ->pause(2000)
                
                // Input kode unik yang benar
                ->type('#pickup_code_input', $pickupCode)
                ->pause(2000)
                
                // Klik tombol verifikasi kode
                ->press('Verifikasi Kode')
                ->pause(2000)
                
                // Tunggu dan pastikan toast sukses / pesan berhasil, lalu tunggu redirect selesai (1.2s timeout in js)
                ->waitForText('Pesanan Berhasil Diserahkan', 10)
                ->pause(2000)
                ->assertQueryStringHas('tab', 'selesai');
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
