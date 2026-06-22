<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-ANL-002 — Isolasi Data Antar Seller: Seller B Tidak Bisa Melihat Data Seller A
 *
 * Memverifikasi bahwa dashboard analitik menerapkan data ownership —
 * Seller B hanya melihat datanya sendiri dan tidak bisa mengakses
 * data pendapatan Seller A, meskipun mengunjungi route yang sama.
 *
 * Precondition (dari setUp):
 *   - 1 Category 'Makanan Test' tersedia di DB.
 *   - 1 User Buyer Global (buyer_global@analytics.test) tersedia di DB.
 *   - Seller A terdaftar dengan toko aktif.
 *   - Seller A memiliki 1 order berstatus 'Selesai' total_amount=100.000.
 *   - Seller B terdaftar dengan toko aktif.
 *   - Seller B TIDAK memiliki order apapun.
 *
 * Login:
 *   - Aktor   : Seller B (bukan Seller A)
 *   - Metode  : ->loginAs($userB)
 *   - Email   : sellerb@analytics.test
 *   - Password: password
 *
 * Steps:
 *   1. Seed: Seller A + toko + order Selesai Rp 100.000.
 *   2. Seed: Seller B + toko (tanpa order).
 *   3. Login sebagai Seller B (loginAs shortcut).
 *   4. Kunjungi route seller.analytics.
 *   5. Periksa nilai pendapatan yang tampil.
 *
 * Input (data seed):
 *   - Seller A: 1 order Selesai, total_amount=100.000
 *   - Seller B: tidak ada order
 *
 * Expected Result:
 *   - assertDontSee('Rp 100.000') — data Seller A tidak bocor ke Seller B.
 *   - assertSee('Rp 0')           — Seller B hanya melihat datanya sendiri (nol).
 */
#[Group('analytics')]
#[Group('TC-ANL-002')]
class TC_ANL_002Test extends AnalyticsTestCase
{
    #[Test]
    public function test_seller_tidak_bisa_melihat_analitik_seller_lain(): void
    {
        // ── Seed: Seller A + toko + order Selesai ──
        $userA   = $this->createSellerUser('Seller A', 'sellera@analytics.test');
        $sellerA = $this->createSellerStore($userA);
        $this->createOrder($sellerA, 'Selesai', 100000);

        // ── Seed: Seller B + toko (tanpa order) ──
        $userB = $this->createSellerUser('Seller B', 'sellerb@analytics.test');
        $this->createSellerStore($userB);

        $this->browse(function (Browser $browser) use ($userB) {
            $browser
                // ── Step 3: Login sebagai Seller B ──
                // Email: sellerb@analytics.test | Password: password
                ->loginAs($userB)

                // ── Step 4: Kunjungi dashboard analitik ──
                ->visitRoute('seller.analytics')

                // ── Assert 1: Data Seller A (Rp 100.000) TIDAK bocor ke Seller B ──
                ->assertDontSee('Rp 100.000')

                // ── Assert 2: Seller B melihat Rp 0 (datanya sendiri) ──
                ->assertSee('Rp 0');
        });
    }
}
