<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * PBI-21: Dasbor Analitik Penjualan (E2E Tests)
 */
class SellerAnalyticsDuskTest extends DuskTestCase
{
    use DatabaseTruncation;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup dasar
        if (Category::count() === 0) {
            Category::create(['name' => 'Makanan Test', 'slug' => 'makanan-test', 'icon' => 'icon.png']);
        }

        $this->buyer = User::factory()->create([
            'name' => 'Buyer Global',
            'email' => 'buyer_global@analytics.test',
            'role' => 'buyer',
        ]);
    }

    private function createSellerUser(string $name, string $email): User
    {
        return User::factory()->create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password'),
            'role' => 'seller',
        ]);
    }

    private function createSellerStore(User $user): Seller
    {
        return Seller::create([
            'user_id' => $user->id,
            'store_name' => 'Toko ' . $user->name,
            'address' => 'Jl. Test Analytics',
            'verification_status' => 'approved',
        ]);
    }

    private function createOrder(Seller $seller, string $status, int $totalAmount): Order
    {
        return Order::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $seller->id,
            'total_amount' => $totalAmount,
            'status' => $status,
            'payment_method' => 'CASH',
        ]);
    }

    private function createOrderItem(Order $order, Seller $seller, int $qty, bool $isSurplus): OrderItem
    {
        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => Category::first()->id,
            'name' => 'Produk ' . ($isSurplus ? 'Surplus' : 'Reguler'),
            'base_price' => 10000,
            'image' => 'dummy.jpg',
        ]);

        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'qty' => $qty,
            'price' => 10000,
            'is_surplus' => $isSurplus,
        ]);
    }

    #[Test]
    #[Group('analytics')]
    #[Group('TC-ANL-006')]
    public function test_analytics_empty_state_for_new_seller(): void
    {
        $user = $this->createSellerUser('Seller Baru', 'new_seller@analytics.test');
        $this->createSellerStore($user);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visitRoute('seller.analytics')
                ->assertSee('Analitik Penjualan')
                ->assertSee('Rp 0')
                ->assertSee('0')
                ->assertSee('Belum ada data penjualan');
        });
    }

    #[Test]
    #[Group('analytics')]
    #[Group('TC-ANL-002')]
    public function test_seller_cannot_see_other_sellers_analytics(): void
    {
        $userA = $this->createSellerUser('Seller A', 'sellera@analytics.test');
        $sellerA = $this->createSellerStore($userA);
        $this->createOrder($sellerA, 'Selesai', 100000);

        $userB = $this->createSellerUser('Seller B', 'sellerb@analytics.test');
        $this->createSellerStore($userB);

        $this->browse(function (Browser $browser) use ($userB) {
            $browser->loginAs($userB)
                ->visitRoute('seller.analytics')
                ->assertDontSee('Rp 100.000')
                ->assertSee('Rp 0');
        });
    }

    #[Test]
    #[Group('analytics')]
    #[Group('TC-ANL-001')]
    public function test_revenue_calculation_only_includes_completed_orders(): void
    {
        $user = $this->createSellerUser('Seller Rev', 'seller_rev@analytics.test');
        $seller = $this->createSellerStore($user);

        // Order Selesai
        $this->createOrder($seller, 'Selesai', 50000);

        // Order Dibatalkan
        $this->createOrder($seller, 'Dibatalkan', 20000);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visitRoute('seller.analytics')
                ->assertSee('Rp 50.000')
                ->assertDontSee('Rp 70.000')
                ->assertDontSee('Rp 20.000');
        });
    }

    #[Test]
    #[Group('analytics')]
    #[Group('TC-ANL-003')]
    public function test_portion_count_accurately_separates_regular_and_surplus(): void
    {
        $user = $this->createSellerUser('Seller Portion', 'seller_portion@analytics.test');
        $seller = $this->createSellerStore($user);

        $order = $this->createOrder($seller, 'Selesai', 50000);
        
        // Item 1: Reguler
        $this->createOrderItem($order, $seller, 3, false);
        // Item 2: Surplus
        $this->createOrderItem($order, $seller, 2, true);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visitRoute('seller.analytics')
                ->assertSeeIn('#card-reguler', '3')
                ->assertSeeIn('#card-surplus', '2')
                ->assertSeeIn('.lg\:col-span-2', '5')
                ->assertSee('TOTAL PORSI');
        });
    }

    #[Test]
    #[Group('analytics')]
    #[Group('TC-ANL-004')]
    public function test_gross_revenue_calculation_accuracy(): void
    {
        $user = $this->createSellerUser('Seller Gross', 'seller_gross@analytics.test');
        $seller = $this->createSellerStore($user);

        // Uji perhitungan matematika tepat
        $this->createOrder($seller, 'Selesai', 125000);
        $this->createOrder($seller, 'Selesai', 25000);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visitRoute('seller.analytics')
                ->assertSee('Rp 150.000');
        });
    }

    #[Test]
    #[Group('analytics')]
    #[Group('TC-ANL-005')]
    public function test_ui_render_cards_and_charts(): void
    {
        $user = $this->createSellerUser('Seller UI', 'seller_ui@analytics.test');
        $seller = $this->createSellerStore($user);
        $order = $this->createOrder($seller, 'Selesai', 10000);
        $this->createOrderItem($order, $seller, 1, false);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visitRoute('seller.analytics')
                ->assertPresent('#card-pendapatan')
                ->assertPresent('#card-surplus')
                ->assertPresent('#card-reguler')
                ->assertPresent('#salesChart'); // Pastikan grafik di-render
        });
    }

    #[Test]
    #[Group('analytics')]
    #[Group('TC-ANL-007')]
    public function test_time_filter_dropdown(): void
    {
        $user = $this->createSellerUser('Seller Filter', 'seller_filter@analytics.test');
        $this->createSellerStore($user);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visitRoute('seller.analytics')
                ->click('#filter-today') // Klik filter hari ini
                ->pause(1000)
                ->assertQueryStringHas('filter', 'today');
        });
    }
}
