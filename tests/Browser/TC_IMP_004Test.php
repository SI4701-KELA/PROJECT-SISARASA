<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-IMP-004 — Akurasi Estimasi Kerugian Finansial yang Dicegah
 *
 * Memverifikasi bahwa kalkulasi "Kerugian Finansial Dicegah" menghasilkan
 * nilai yang tepat berdasarkan rumus: SUM(qty × price) untuk item surplus
 * dari pesanan berstatus 'Selesai'.
 *
 * Rumus: qty × price = 2 × 15.000 = 30.000
 *
 * Precondition (dari setUp):
 *   - 1 Category 'Makanan Test' tersedia di DB.
 *   - 1 User Buyer Global (buyer_global_impact@analytics.test) tersedia di DB.
 *   - Admin terdaftar (email unik via uniqid).
 *   - Toko Uang terdaftar dengan verification_status=approved.
 *   - 1 Order berstatus 'Selesai' berisi 1 OrderItem surplus:
 *       qty=2, is_surplus=true, price=15.000
 *
 * Login:
 *   - Aktor   : Admin
 *   - Metode  : ->loginAs($admin)
 *   - Email   : admin_impact_{uniqid}@test.com
 *   - Password: password
 *
 * Steps:
 *   1. Seed: Admin + Seller + Toko Uang.
 *   2. Seed: 1 Order Selesai.
 *   3. Seed: 1 item surplus: qty=2, price=15.000.
 *   4. Login sebagai Admin.
 *   5. Kunjungi route admin.impact-tracker.
 *   6. Tunggu 1500ms.
 *   7. Periksa nilai di elemen #card-financial.
 *
 * Input (data seed):
 *   - OrderItem: qty=2, is_surplus=true, price=15.000
 *   - Kalkulasi: 2 × 15.000 = 30.000
 *
 * Expected Result:
 *   - assertSeeIn('#card-financial', '30.000') — nilai finansial tepat.
 */
#[Group('impact')]
#[Group('TC-IMP-004')]
class TC_IMP_004Test extends ImpactTrackerTestCase
{
    #[Test]
    public function test_akurasi_estimasi_kerugian_finansial_dicegah(): void
    {
        // ── Seed: Admin ──
        $admin = $this->createAdminUser();

        // ── Seed: Seller & Toko Uang ──
        $seller = $this->createSellerStore($this->createSellerUser('Toko Uang'), 'Toko Uang');

        // ── Seed: 1 Order Selesai ──
        $order = $this->createOrder($seller, 'Selesai');

        // ── Seed: 1 item surplus — qty=2, price=15.000 ──
        // Kalkulasi finansial: 2 × 15.000 = 30.000
        $this->createOrderItem($order, $seller, 2, true, 15000);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser
                // ── Step 4: Login sebagai Admin ──
                // Email: admin_impact_{uniqid}@test.com | Password: password
                ->loginAs($admin)

                // ── Step 5: Kunjungi halaman Impact Tracker ──
                ->visitRoute('admin.impact-tracker')

                // ── Step 6: Tunggu rendering selesai ──
                ->pause(1500)

                // ── Assert: Kerugian finansial = Rp 30.000 (2 × 15.000) ──
                ->assertSeeIn('#card-financial', '30.000');
        });
    }
}
