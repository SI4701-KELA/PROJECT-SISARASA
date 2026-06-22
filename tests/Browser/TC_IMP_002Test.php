<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-IMP-002 — Filter Status Pesanan: Hanya Pesanan "Selesai" yang Dihitung
 *
 * Memverifikasi bahwa Impact Tracker HANYA menghitung item surplus dari
 * pesanan berstatus 'Selesai' dan MENGABAIKAN pesanan berstatus 'Dibatalkan'
 * maupun 'Diproses', meskipun item di dalamnya berjenis surplus.
 *
 * Precondition (dari setUp):
 *   - 1 Category 'Makanan Test' tersedia di DB.
 *   - 1 User Buyer Global (buyer_global_impact@analytics.test) tersedia di DB.
 *   - Admin terdaftar (email unik via uniqid).
 *   - Toko Impact terdaftar dengan verification_status=approved.
 *   - 3 Order di-seed ke DB masing-masing dengan status berbeda:
 *       Order 1: status='Selesai',    1 item surplus qty=2
 *       Order 2: status='Dibatalkan', 1 item surplus qty=2
 *       Order 3: status='Diproses',   1 item surplus qty=2
 *
 * Login:
 *   - Aktor   : Admin
 *   - Metode  : ->loginAs($admin)
 *   - Email   : admin_impact_{uniqid}@test.com
 *   - Password: password
 *
 * Steps:
 *   1. Seed: Admin + Seller + Toko.
 *   2. Seed: Order 1 status='Selesai'    + item surplus qty=2.
 *   3. Seed: Order 2 status='Dibatalkan' + item surplus qty=2.
 *   4. Seed: Order 3 status='Diproses'   + item surplus qty=2.
 *   5. Login sebagai Admin.
 *   6. Kunjungi route admin.impact-tracker.
 *   7. Tunggu 1500ms.
 *   8. Periksa nilai di #hero-food-saved.
 *
 * Input (data seed):
 *   - Order Selesai   : qty=2 surplus → DIHITUNG
 *   - Order Dibatalkan: qty=2 surplus → DIABAIKAN
 *   - Order Diproses  : qty=2 surplus → DIABAIKAN
 *   - Ekspektasi total: hanya 2 porsi (dari order Selesai)
 *
 * Expected Result:
 *   - assertSeeIn('#hero-food-saved',    '2') — hanya order Selesai.
 *   - assertDontSeeIn('#hero-food-saved', '6') — total ketiganya tidak dijumlah.
 */
#[Group('impact')]
#[Group('TC-IMP-002')]
class TC_IMP_002Test extends ImpactTrackerTestCase
{
    #[Test]
    public function test_impact_tracker_mengabaikan_pesanan_batal_atau_diproses(): void
    {
        // ── Seed: Admin ──
        $admin = $this->createAdminUser();

        // ── Seed: Seller & Toko ──
        $user   = $this->createSellerUser('Toko Impact');
        $seller = $this->createSellerStore($user, 'Toko Impact');

        // ── Seed: Order 1 — Selesai (DIHITUNG) ──
        $order1 = $this->createOrder($seller, 'Selesai');
        $this->createOrderItem($order1, $seller, 2, true, 10000);

        // ── Seed: Order 2 — Dibatalkan (DIABAIKAN) ──
        $order2 = $this->createOrder($seller, 'Dibatalkan');
        $this->createOrderItem($order2, $seller, 2, true, 10000);

        // ── Seed: Order 3 — Diproses (DIABAIKAN) ──
        $order3 = $this->createOrder($seller, 'Diproses');
        $this->createOrderItem($order3, $seller, 2, true, 10000);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser
                // ── Step 5: Login sebagai Admin ──
                // Email: admin_impact_{uniqid}@test.com | Password: password
                ->loginAs($admin)

                // ── Step 6: Kunjungi halaman Impact Tracker ──
                ->visitRoute('admin.impact-tracker')

                // ── Step 7: Tunggu rendering selesai ──
                ->pause(1500)

                // ── Assert 1: Hanya 2 porsi dari order Selesai yang terhitung ──
                ->assertSeeIn('#hero-food-saved', '2')

                // ── Assert 2: Total seluruh order (6) TIDAK dijumlahkan ──
                ->assertDontSeeIn('#hero-food-saved', '6');
        });
    }
}
