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
 * Base class untuk semua test PBI-22 (Laporan Statistik Penyelamatan Makanan).
 *
 * setUp() otomatis membuat:
 *   - 1 Category global ('Makanan Test') — dibuat sekali jika belum ada.
 *   - 1 User Buyer Global (buyer_global_impact@analytics.test).
 *
 * Kredensial Admin:
 *   - Email  : admin_impact_{uniqid}@test.com  (unik tiap test, cegah konflik)
 *   - Password: password
 *   - Login  : ->loginAs($admin) shortcut Dusk
 */
abstract class ImpactTrackerTestCase extends DuskTestCase
{
    use DatabaseTruncation;

    /** User Buyer global — dipakai semua createOrder() */
    protected User $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat 1 Category global (idempotent)
        if (Category::count() === 0) {
            Category::create([
                'name' => 'Makanan Test',
                'slug' => 'makanan-test',
                'icon' => 'icon.png',
            ]);
        }

        // Buyer global dummy untuk semua order
        $this->buyer = User::factory()->create([
            'name'  => 'Buyer Global',
            'email' => 'buyer_global_impact@analytics.test',
            'role'  => 'buyer',
        ]);
    }

    // ─── Factory Helpers ─────────────────────────────────────────

    /**
     * Buat Admin — email unik via uniqid() agar tidak konflik antar test paralel.
     */
    protected function createAdminUser(): User
    {
        return User::factory()->create([
            'name'     => 'Admin Sisa Rasa',
            'email'    => 'admin_impact_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);
    }

    /**
     * Buat Seller User — email otomatis dari nama + uniqid().
     */
    protected function createSellerUser(string $name): User
    {
        return User::factory()->create([
            'name'     => $name,
            'email'    => strtolower(str_replace(' ', '', $name)) . '_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role'     => 'seller',
        ]);
    }

    /**
     * Buat Seller (Toko) berdasarkan User dan nama toko.
     */
    protected function createSellerStore(User $user, string $storeName): Seller
    {
        return Seller::create([
            'user_id'             => $user->id,
            'store_name'          => $storeName,
            'address'             => 'Jl. Test Impact Tracker',
            'verification_status' => 'approved',
        ]);
    }

    /**
     * Buat Order dengan status tertentu.
     * total_amount default 100.000.
     */
    protected function createOrder(Seller $seller, string $status): Order
    {
        return Order::create([
            'buyer_id'       => $this->buyer->id,
            'seller_id'      => $seller->id,
            'total_amount'   => 100000,
            'status'         => $status,
            'payment_method' => 'CASH',
        ]);
    }

    /**
     * Buat OrderItem (surplus atau reguler) dalam sebuah order.
     *
     * @param int  $qty       Jumlah porsi.
     * @param bool $isSurplus true = surplus, false = reguler.
     * @param int  $price     Harga per porsi (default 10.000).
     */
    protected function createOrderItem(Order $order, Seller $seller, int $qty, bool $isSurplus, int $price = 10000): OrderItem
    {
        $product = Product::create([
            'seller_id'   => $seller->id,
            'category_id' => Category::first()->id,
            'name'        => 'Produk ' . ($isSurplus ? 'Surplus' : 'Reguler'),
            'base_price'  => $price,
            'image'       => 'dummy.jpg',
        ]);

        return OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'seller_id'  => $seller->id,
            'qty'        => $qty,
            'price'      => $price,
            'is_surplus' => $isSurplus,
        ]);
    }
}
