<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;

class TransactionHistoryTest extends DuskTestCase
{
    /**
     * TS.PBI.004 - Akses Riwayat Transaksi (Positive)
     * Menampilkan daftar pesanan yang sudah selesai.
     */
    public function test_buyer_views_completed_transaction_history(): void
    {
        $order = $this->createCompletedOrder();
        $buyer = $order->buyer;

        $this->browse(function (Browser $browser) use ($buyer, $order): void {
            $browser->loginAs($buyer)
                ->visit('/buyer/orders?tab=riwayat') // Halaman riwayat pesanan
                ->waitForText($order->seller->store_name, 10)
                ->assertSee($order->seller->store_name)
                ->assertSee(number_format($order->total_amount, 0, ',', '.'));
        });
    }

    /**
     * TS.PBI.004 - Tampil Empty State (Positive)
     * Menampilkan pesan kosong saat riwayat belum ada.
     */
    public function test_buyer_views_empty_transaction_history(): void
    {
        // Buat buyer baru yang bersih tanpa transaksi apa pun
        $emptyBuyer = User::factory()->create(['role' => 'buyer']);

        $this->browse(function (Browser $browser) use ($emptyBuyer): void {
            $browser->loginAs($emptyBuyer)
                ->visit('/buyer/orders?tab=riwayat')
                ->waitForText('Belum ada riwayat pesanan', 10)
                ->assertSee('Belum ada riwayat pesanan');
        });
    }

    /**
     * Helper: Membuat transaksi selesai
     */
    private function createCompletedOrder(): Order
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Kedai Siti Jaya',
            'verification_status' => 'approved',
            'address' => 'Jl. Gatot Subroto No. 5, Jakarta',
            'latitude' => -6.2341,
            'longitude' => 106.7993,
        ]);
        
        $category = Category::firstOrCreate(['name' => 'Makanan Berat']);
        
        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Roti Bakar Premium',
            'description' => 'Roti bakar lezat rasa coklat keju',
            'base_price' => 12000,
            'image' => 'products/dummy.jpg',
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'total_amount' => 12000,
            'payment_method' => 'cash',
            'status' => 'selesai',
            'pickup_code' => 'SISA-66666',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 12000,
        ]);

        return $order;
    }
}
