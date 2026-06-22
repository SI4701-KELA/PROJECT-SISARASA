<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-ANL-003 — Pemisahan Hitungan Porsi: Reguler vs Surplus Ditampilkan Terpisah
 *
 * Memverifikasi bahwa dashboard analitik memisahkan jumlah porsi
 * produk reguler dan produk surplus secara akurat, serta menampilkan
 * total porsi gabungan di elemen yang berbeda.
 *
 * Precondition (dari setUp):
 *   - 1 Category 'Makanan Test' tersedia di DB.
 *   - 1 User Buyer Global (buyer_global@analytics.test) tersedia di DB.
 *   - Seller Portion terdaftar dengan toko aktif.
 *   - 1 Order berstatus 'Selesai' di-seed.
 *   - 2 OrderItem di-seed dalam order tersebut:
 *       Item 1: qty=3, is_surplus=false (Produk Reguler)
 *       Item 2: qty=2, is_surplus=true  (Produk Surplus)
 *
 * Login:
 *   - Aktor   : Seller Portion
 *   - Metode  : ->loginAs($user)
 *   - Email   : seller_portion@analytics.test
 *   - Password: password
 *
 * Steps:
 *   1. Seed: Seller Portion + toko + 1 order Selesai.
 *   2. Seed: Item reguler qty=3 (is_surplus=false).
 *   3. Seed: Item surplus qty=2 (is_surplus=true).
 *   4. Login sebagai Seller Portion (loginAs shortcut).
 *   5. Kunjungi route seller.analytics.
 *   6. Periksa nilai di kartu #card-reguler, #card-surplus, dan total.
 *
 * Input (data seed):
 *   - OrderItem 1: qty=3, is_surplus=false  → masuk ke #card-reguler
 *   - OrderItem 2: qty=2, is_surplus=true   → masuk ke #card-surplus
 *   - Total porsi : 3 + 2 = 5              → tampil di .lg:col-span-2
 *
 * Expected Result:
 *   - assertSeeIn('#card-reguler',     '3')   — porsi reguler tepat.
 *   - assertSeeIn('#card-surplus',     '2')   — porsi surplus tepat.
 *   - assertSeeIn('.lg\\:col-span-2',  '5')   — total porsi = 3 + 2.
 *   - assertSee('TOTAL PORSI')                — label heading total ada.
 */
#[Group('analytics')]
#[Group('TC-ANL-003')]
class TC_ANL_003Test extends AnalyticsTestCase
{
    #[Test]
    public function test_hitungan_porsi_memisahkan_reguler_dan_surplus(): void
    {
        // ── Seed: Seller Portion + toko ──
        $user   = $this->createSellerUser('Seller Portion', 'seller_portion@analytics.test');
        $seller = $this->createSellerStore($user);

        // ── Seed: 1 Order Selesai ──
        $order = $this->createOrder($seller, 'Selesai', 50000);

        // ── Seed: Item 1 — Reguler, qty=3 ──
        $this->createOrderItem($order, $seller, 3, false);

        // ── Seed: Item 2 — Surplus, qty=2 ──
        $this->createOrderItem($order, $seller, 2, true);

        $this->browse(function (Browser $browser) use ($user) {
            $browser
                // ── Step 4: Login sebagai Seller Portion ──
                // Email: seller_portion@analytics.test | Password: password
                ->loginAs($user)

                // ── Step 5: Kunjungi dashboard analitik ──
                ->visitRoute('seller.analytics')

                // ── Assert 1: Kartu reguler menampilkan angka 3 ──
                ->assertSeeIn('#card-reguler', '3')

                // ── Assert 2: Kartu surplus menampilkan angka 2 ──
                ->assertSeeIn('#card-surplus', '2')

                // ── Assert 3: Area total porsi menampilkan 5 (3 + 2) ──
                ->assertSeeIn('.lg\\:col-span-2', '5')

                // ── Assert 4: Label "TOTAL PORSI" ada di halaman ──
                ->assertSee('TOTAL PORSI');
        });
    }
}
