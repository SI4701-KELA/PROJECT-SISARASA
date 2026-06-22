<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Tests\DuskTestCase;

/**
 * Base class untuk semua test PBI-21 (Dashboard Laporan Penjualan).
 *
 * setUp() otomatis membuat:
 *   - 1 Category global ('Makanan Test') — dibuat sekali jika belum ada.
 *   - 1 User Buyer global (buyer_global@analytics.test) — dipakai oleh semua createOrder().
 *
 * Kredensial Seller (berbeda tiap TC agar tidak konflik):
 *   - Email  : <prefix>@analytics.test
 *   - Password: password
 */
abstract class AnalyticsTestCase extends DuskTestCase
{
    use DatabaseTruncation;

    /** User Buyer global — dipakai oleh semua createOrder() */
    protected User $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat 1 Category global (idempotent — hanya buat jika belum ada)
        if (Category::count() === 0) {
            Category::create([
                'name' => 'Makanan Test',
                'slug' => 'makanan-test',
                'icon' => 'icon.png',
            ]);
        }

        // Buyer global yang dipakai semua order di setiap TC
        $this->buyer = User::factory()->create([
            'name'  => 'Buyer Global',
            'email' => 'buyer_global@analytics.test',
            'role'  => 'buyer',
        ]);
    }

    // ─── Factory Helpers ─────────────────────────────────────────

    protected function createSellerUser(string $name, string $email): User
    {
        return User::factory()->create([
            'name'     => $name,
            'email'    => $email,
            'password' => bcrypt('password'),
            'role'     => 'seller',
        ]);
    }

    protected function createSellerStore(User $user): Seller
    {
        return Seller::create([
            'user_id'             => $user->id,
            'store_name'          => 'Toko ' . $user->name,
            'address'             => 'Jl. Test Analytics',
            'verification_status' => 'approved',
        ]);
    }

    protected function createOrder(Seller $seller, string $status, int $totalAmount): Order
    {
        return Order::create([
            'buyer_id'       => $this->buyer->id,
            'seller_id'      => $seller->id,
            'total_amount'   => $totalAmount,
            'status'         => $status,
            'payment_method' => 'CASH',
        ]);
    }

    protected function createOrderItem(Order $order, Seller $seller, int $qty, bool $isSurplus): OrderItem
    {
        $product = Product::create([
            'seller_id'   => $seller->id,
            'category_id' => Category::first()->id,
            'name'        => 'Produk ' . ($isSurplus ? 'Surplus' : 'Reguler'),
            'base_price'  => 10000,
            'image'       => 'dummy.jpg',
        ]);

        return OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'seller_id'  => $seller->id,
            'qty'        => $qty,
            'price'      => 10000,
            'is_surplus' => $isSurplus,
        ]);
    }
}
