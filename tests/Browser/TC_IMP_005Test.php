<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-IMP-005 — Hitungan Unik UMKM Berkontribusi (COUNT DISTINCT seller_id)
 *
 * Memverifikasi bahwa jumlah "UMKM Berkontribusi Aktif" menggunakan logika
 * COUNT DISTINCT pada seller_id — bukan menjumlahkan total order.
 *
 * Skenario: 3 seller berbeda, total 5 order Selesai, ekspektasi UMKM = 3 (bukan 5).
 *
 * Precondition (dari setUp):
 *   - 1 Category 'Makanan Test' tersedia di DB.
 *   - 1 User Buyer Global (buyer_global_impact@analytics.test) tersedia di DB.
 *   - Admin terdaftar (email unik via uniqid).
 *   - 3 Seller (Toko A, Toko B, Toko C) terdaftar dengan toko aktif.
 *   - Data order di-seed:
 *       Toko A: 2 order Selesai (masing-masing 1 item surplus qty=1)
 *       Toko B: 2 order Selesai (masing-masing 1 item surplus qty=1)
 *       Toko C: 1 order Selesai (1 item surplus qty=1)
 *       Total: 5 order dari 3 seller unik
 *
 * Login:
 *   - Aktor   : Admin
 *   - Metode  : ->loginAs($admin)
 *   - Email   : admin_impact_{uniqid}@test.com
 *   - Password: password
 *
 * Steps:
 *   1. Seed: Admin + 3 Seller + 3 Toko.
 *   2. Seed: Toko A → 2 order Selesai, masing-masing 1 item surplus.
 *   3. Seed: Toko B → 2 order Selesai, masing-masing 1 item surplus.
 *   4. Seed: Toko C → 1 order Selesai, 1 item surplus.
 *   5. Login sebagai Admin.
 *   6. Kunjungi route admin.impact-tracker.
 *   7. Tunggu 1500ms.
 *   8. Periksa nilai di #card-umkm.
 *
 * Input (data seed):
 *   - 3 Seller unik, 5 total order Selesai
 *   - COUNT DISTINCT seller_id = 3
 *
 * Expected Result:
 *   - assertSeeIn('#card-umkm',    '3') — COUNT DISTINCT seller aktif.
 *   - assertDontSeeIn('#card-umkm', '5') — total order tidak digunakan.
 */
#[Group('impact')]
#[Group('TC-IMP-005')]
class TC_IMP_005Test extends ImpactTrackerTestCase
{
    #[Test]
    public function test_total_kontribusi_umkm_menggunakan_hitungan_unik(): void
    {
        // ── Seed: Admin ──
        $admin = $this->createAdminUser();

        // ── Seed: 3 Seller & 3 Toko ──
        $sellerA = $this->createSellerStore($this->createSellerUser('Toko A'), 'Toko A');
        $sellerB = $this->createSellerStore($this->createSellerUser('Toko B'), 'Toko B');
        $sellerC = $this->createSellerStore($this->createSellerUser('Toko C'), 'Toko C');

        // ── Seed: Toko A — 2 order Selesai (masing-masing 1 item surplus) ──
        $orderA1 = $this->createOrder($sellerA, 'Selesai');
        $this->createOrderItem($orderA1, $sellerA, 1, true, 10000);
        $orderA2 = $this->createOrder($sellerA, 'Selesai');
        $this->createOrderItem($orderA2, $sellerA, 1, true, 10000);

        // ── Seed: Toko B — 2 order Selesai (masing-masing 1 item surplus) ──
        $orderB1 = $this->createOrder($sellerB, 'Selesai');
        $this->createOrderItem($orderB1, $sellerB, 1, true, 10000);
        $orderB2 = $this->createOrder($sellerB, 'Selesai');
        $this->createOrderItem($orderB2, $sellerB, 1, true, 10000);

        // ── Seed: Toko C — 1 order Selesai (1 item surplus) ──
        $orderC1 = $this->createOrder($sellerC, 'Selesai');
        $this->createOrderItem($orderC1, $sellerC, 1, true, 10000);

        // Total: 5 order dari 3 seller unik — ekspektasi UMKM = 3

        $this->browse(function (Browser $browser) use ($admin) {
            $browser
                // ── Step 5: Login sebagai Admin ──
                // Email: admin_impact_{uniqid}@test.com | Password: password
                ->loginAs($admin)

                // ── Step 6: Kunjungi halaman Impact Tracker ──
                ->visitRoute('admin.impact-tracker')

                // ── Step 7: Tunggu rendering selesai ──
                ->pause(1500)

                // ── Assert 1: UMKM = 3 (COUNT DISTINCT seller) ──
                ->assertSeeIn('#card-umkm', '3')

                // ── Assert 2: Total order (5) TIDAK digunakan sebagai jumlah UMKM ──
                ->assertDontSeeIn('#card-umkm', '5');
        });
    }
}
