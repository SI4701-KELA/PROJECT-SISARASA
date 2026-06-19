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
 * PBI-22: Impact Tracker (E2E Tests)
 */
class ImpactTrackerDuskTest extends DuskTestCase
{
    use DatabaseTruncation;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup data master Category secara aman (jika belum ter-seed)
        if (Category::count() === 0) {
            Category::create(['name' => 'Makanan Test', 'slug' => 'makanan-test', 'icon' => 'icon.png']);
        }

        // Siapkan global buyer dummy yang dapat digunakan di semua test order
        $this->buyer = User::factory()->create([
            'name' => 'Buyer Global',
            'email' => 'buyer_global_impact@analytics.test',
            'role' => 'buyer',
        ]);
    }

    // --- HELPER UNTUK SETUP DATA SIMULASI SECARA CEPAT ---

    /**
     * Membuat data User Admin.
     */
    private function createAdminUser(): User
    {
        return User::factory()->create([
            'name' => 'Admin Sisa Rasa',
            'email' => 'admin_impact_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    /**
     * Membuat data User Seller.
     */
    private function createSellerUser(string $name): User
    {
        return User::factory()->create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '', $name)) . '_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
        ]);
    }

    /**
     * Membuat data Seller (Toko).
     */
    private function createSellerStore(User $user, string $storeName): Seller
    {
        return Seller::create([
            'user_id' => $user->id,
            'store_name' => $storeName,
            'address' => 'Jl. Test Impact Tracker',
            'verification_status' => 'approved',
        ]);
    }

    /**
     * Membuat data Order (Pesanan).
     */
    private function createOrder(Seller $seller, string $status): Order
    {
        return Order::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $seller->id,
            'total_amount' => 100000,
            'status' => $status,
            'payment_method' => 'CASH',
        ]);
    }

    /**
     * Membuat data Order Item (Item Pesanan).
     */
    private function createOrderItem(Order $order, Seller $seller, int $qty, bool $isSurplus, int $price = 10000): OrderItem
    {
        // Secara dinamis siapkan produk berdasarkan tipe order item
        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => Category::first()->id,
            'name' => 'Produk ' . ($isSurplus ? 'Surplus' : 'Reguler'),
            'base_price' => $price,
            'image' => 'dummy.jpg',
        ]);

        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'qty' => $qty,
            'price' => $price,
            'is_surplus' => $isSurplus,
        ]);
    }

    // --- TEST CASES ---

    /**
     * TC-IMP-001: Menguji Filter Tipe Produk
     */
    #[Test]
    #[Group('impact')]
    #[Group('TC-IMP-001')]
    public function test_impact_tracker_hanya_menghitung_item_surplus(): void
    {
        $admin = $this->createAdminUser();
        $user = $this->createSellerUser('Toko Sisa Rasa');
        $seller = $this->createSellerStore($user, 'Toko Sisa Rasa');

        // Skenario: 1 Order "Selesai"
        $order = $this->createOrder($seller, 'Selesai');
        
        // Isi dengan 10 porsi item Reguler
        $this->createOrderItem($order, $seller, 10, false, 10000);
        // Isi dengan 5 porsi item Surplus
        $this->createOrderItem($order, $seller, 5, true, 10000);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visitRoute('admin.impact-tracker')
                ->pause(1500)
                // Memastikan angka 10 diabaikan dan hanya 5 porsi Surplus yang tampil
                ->assertSeeIn('#hero-food-saved', '5')
                // Memastikan "Kerugian Finansial yang Dicegah" hanya menghitung surplus (5 x 10.000)
                ->assertSeeIn('#card-financial', '50.000');
        });
    }

    /**
     * TC-IMP-002: Menguji Filter Status Pesanan
     */
    #[Test]
    #[Group('impact')]
    #[Group('TC-IMP-002')]
    public function test_impact_tracker_mengabaikan_pesanan_batal_atau_diproses(): void
    {
        $admin = $this->createAdminUser();
        $user = $this->createSellerUser('Toko Impact');
        $seller = $this->createSellerStore($user, 'Toko Impact');

        // Order 1: Status "Selesai" (2 porsi Surplus)
        $order1 = $this->createOrder($seller, 'Selesai');
        $this->createOrderItem($order1, $seller, 2, true, 10000);

        // Order 2: Status "Dibatalkan" (2 porsi Surplus)
        $order2 = $this->createOrder($seller, 'Dibatalkan');
        $this->createOrderItem($order2, $seller, 2, true, 10000);

        // Order 3: Status "Diproses" (2 porsi Surplus)
        $order3 = $this->createOrder($seller, 'Diproses');
        $this->createOrderItem($order3, $seller, 2, true, 10000);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visitRoute('admin.impact-tracker')
                ->pause(1500)
                // HANYA boleh menampilkan angka "2"
                ->assertSeeIn('#hero-food-saved', '2')
                // Memastikan tidak menghitung total seluruh order (6 porsi)
                ->assertDontSeeIn('#hero-food-saved', '6');
        });
    }

    /**
     * TC-IMP-005: Menguji Logika Total UMKM Berkontribusi
     */
    #[Test]
    #[Group('impact')]
    #[Group('TC-IMP-005')]
    public function test_total_kontribusi_umkm_menggunakan_hitungan_unik(): void
    {
        $admin = $this->createAdminUser();
        
        // Buat 3 Seller
        $sellerA = $this->createSellerStore($this->createSellerUser('Toko A'), 'Toko A');
        $sellerB = $this->createSellerStore($this->createSellerUser('Toko B'), 'Toko B');
        $sellerC = $this->createSellerStore($this->createSellerUser('Toko C'), 'Toko C');

        // Toko A: 2 Order "Selesai" (surplus)
        $orderA1 = $this->createOrder($sellerA, 'Selesai');
        $this->createOrderItem($orderA1, $sellerA, 1, true, 10000);
        $orderA2 = $this->createOrder($sellerA, 'Selesai');
        $this->createOrderItem($orderA2, $sellerA, 1, true, 10000);

        // Toko B: 2 Order "Selesai" (surplus)
        $orderB1 = $this->createOrder($sellerB, 'Selesai');
        $this->createOrderItem($orderB1, $sellerB, 1, true, 10000);
        $orderB2 = $this->createOrder($sellerB, 'Selesai');
        $this->createOrderItem($orderB2, $sellerB, 1, true, 10000);

        // Toko C: 1 Order "Selesai" (surplus)
        $orderC1 = $this->createOrder($sellerC, 'Selesai');
        $this->createOrderItem($orderC1, $sellerC, 1, true, 10000);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visitRoute('admin.impact-tracker')
                ->pause(1500)
                // Memastikan sistem melakukan COUNT DISTINCT dengan benar (Hanya 3 Toko)
                ->assertSeeIn('#card-umkm', '3')
                // Tidak boleh menampilkan total order (5) sebagai jumlah UMKM
                ->assertDontSeeIn('#card-umkm', '5');
        });
    }

    #[Test]
    #[Group('impact')]
    #[Group('TC-IMP-003')]
    public function test_akurasi_jumlah_porsi_makanan(): void
    {
        $admin = $this->createAdminUser();
        $seller = $this->createSellerStore($this->createSellerUser('Toko QTY'), 'Toko QTY');

        // Total 7 porsi surplus (3 + 4)
        $order = $this->createOrder($seller, 'Selesai');
        $this->createOrderItem($order, $seller, 3, true);
        $this->createOrderItem($order, $seller, 4, true);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visitRoute('admin.impact-tracker')
                ->pause(1500)
                ->assertSeeIn('#hero-food-saved', '7');
        });
    }

    #[Test]
    #[Group('impact')]
    #[Group('TC-IMP-004')]
    public function test_akurasi_estimasi_kerugian_finansial_dicegah(): void
    {
        $admin = $this->createAdminUser();
        $seller = $this->createSellerStore($this->createSellerUser('Toko Uang'), 'Toko Uang');

        $order = $this->createOrder($seller, 'Selesai');
        // 2 porsi x 15000 = 30000
        $this->createOrderItem($order, $seller, 2, true, 15000);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visitRoute('admin.impact-tracker')
                ->pause(1500)
                ->assertSeeIn('#card-financial', '30.000');
        });
    }

    #[Test]
    #[Group('impact')]
    #[Group('TC-IMP-006')]
    public function test_update_data_realtime_saat_pesanan_selesai(): void
    {
        $admin = $this->createAdminUser();
        $seller = $this->createSellerStore($this->createSellerUser('Toko Realtime'), 'Toko Realtime');

        // Order 1 Selesai
        $order1 = $this->createOrder($seller, 'Selesai');
        $this->createOrderItem($order1, $seller, 1, true);

        $this->browse(function (Browser $browser) use ($admin, $seller) {
            $browser->loginAs($admin)
                ->visitRoute('admin.impact-tracker')
                ->pause(1500)
                ->assertSeeIn('#hero-food-saved', '1');

            // Order 2 diproses jadi Selesai (Simulasi Backend Update)
            $order2 = $this->createOrder($seller, 'Selesai');
            $this->createOrderItem($order2, $seller, 5, true);

            // Refresh halaman memastikan data bertambah 1 + 5 = 6
            $browser->refresh()
                ->pause(1500)
                ->assertSeeIn('#hero-food-saved', '6');
        });
    }

    #[Test]
    #[Group('impact')]
    #[Group('TC-IMP-007')]
    public function test_visualisasi_makro_dan_tipografi(): void
    {
        $admin = $this->createAdminUser();
        $seller = $this->createSellerStore($this->createSellerUser('Toko Visual'), 'Toko Visual');
        $order = $this->createOrder($seller, 'Selesai');
        $this->createOrderItem($order, $seller, 1, true);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visitRoute('admin.impact-tracker')
                ->pause(1500)
                ->assertPresent('.rank-badge') // Memastikan badge render
                ->assertSee('Porsi Makanan Berhasil Diselamatkan')
                ->assertSee('Kerugian Finansial Dicegah')
                ->assertSee('UMKM Berkontribusi Aktif');
        });
    }
}
