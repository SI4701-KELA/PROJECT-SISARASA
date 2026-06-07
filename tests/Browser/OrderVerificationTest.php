<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;

class OrderVerificationTest extends DuskTestCase
{
    /**
     * TS.PBI.018 - Generator QR Code (Positive)
     * Menampilkan QR Code pada pesanan siap ambil.
     */
    public function test_qr_code_generator_displays_correctly(): void
    {
        $order = $this->createOrderWithStatus('siap_diambil', 'SISA-99999');
        $buyer = $order->buyer;

        $this->browse(function (Browser $browser) use ($buyer, $order): void {
            $browser->loginAs($buyer)
                ->visit("/buyer/orders/{$order->id}") // Halaman pelacakan pesanan buyer
                ->waitFor('#qrcode-canvas', 10)
                ->assertPresent('#qrcode-canvas') // QR Code Canvas muncul
                ->assertSee($order->pickup_code); // Kode unik teks muncul
        });
    }

    /**
     * TS.PBI.018 - Verifikasi Penjual (Positive)
     * Seller melakukan verifikasi penyerahan makanan dengan kode benar.
     */
    public function test_seller_verifies_order_successfully(): void
    {
        $order = $this->createOrderWithStatus('siap_diambil', 'SISA-88888');
        $sellerUser = User::find($order->seller->user_id);

        $this->browse(function (Browser $browser) use ($sellerUser, $order): void {
            $browser->loginAs($sellerUser)
                ->visit('/seller/orders?tab=siap') // Tab siap diambil
                ->waitFor('.btn-verifikasi-pengambilan', 10)
                ->click('.btn-verifikasi-pengambilan') // Klik tombol verifikasi
                ->waitFor('#verify-modal', 5)
                
                // Klik tab input manual
                ->press('Input Manual')
                ->waitFor('#pickup_code_input', 5)
                
                // Input kode unik yang benar
                ->type('#pickup_code_input', 'SISA-88888')
                
                // Klik tombol verifikasi kode
                ->press('Verifikasi Kode')
                
                // Tunggu dan pastikan toast sukses / pesan berhasil
                ->waitForText('Pesanan Berhasil Diserahkan', 10);
        });
    }

    /**
     * TS.PBI.018 - Validasi Kode Salah (Negative)
     * Seller menginput kode unik yang salah/tidak terdaftar.
     */
    public function test_seller_verify_with_wrong_code_fails(): void
    {
        $order = $this->createOrderWithStatus('siap_diambil', 'SISA-77777');
        $sellerUser = User::find($order->seller->user_id);

        $this->browse(function (Browser $browser) use ($sellerUser): void {
            $browser->loginAs($sellerUser)
                ->visit('/seller/orders?tab=siap')
                ->waitFor('.btn-verifikasi-pengambilan', 10)
                ->click('.btn-verifikasi-pengambilan')
                ->waitFor('#verify-modal', 5)
                
                // Klik tab input manual
                ->press('Input Manual')
                ->waitFor('#pickup_code_input', 5)
                
                // Input kode unik salah
                ->type('#pickup_code_input', 'SALAH123')
                ->press('Verifikasi Kode')
                
                // Muncul pesan error
                ->waitForText('Kode tidak valid!', 10);
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
