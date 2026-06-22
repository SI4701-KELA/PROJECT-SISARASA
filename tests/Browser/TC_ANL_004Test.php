<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-ANL-004 — Akurasi Penjumlahan Pendapatan dari Beberapa Pesanan
 *
 * Memverifikasi bahwa sistem menjumlahkan total_amount dari SEMUA pesanan
 * berstatus 'Selesai' secara akurat ketika terdapat lebih dari satu pesanan.
 *
 * Precondition (dari setUp):
 *   - 1 Category 'Makanan Test' tersedia di DB.
 *   - 1 User Buyer Global (buyer_global@analytics.test) tersedia di DB.
 *   - Seller Gross terdaftar dengan toko aktif.
 *   - 2 Order berstatus 'Selesai' di-seed ke DB:
 *       Order 1: total_amount=125.000
 *       Order 2: total_amount=25.000
 *       Total ekspektasi: 125.000 + 25.000 = 150.000
 *
 * Login:
 *   - Aktor   : Seller Gross
 *   - Metode  : ->loginAs($user)
 *   - Email   : seller_gross@analytics.test
 *   - Password: password
 *
 * Steps:
 *   1. Seed: Seller Gross + toko.
 *   2. Seed: Order 1 Selesai total_amount=125.000.
 *   3. Seed: Order 2 Selesai total_amount=25.000.
 *   4. Login sebagai Seller Gross (loginAs shortcut).
 *   5. Kunjungi route seller.analytics.
 *   6. Periksa total pendapatan yang tampil di dashboard.
 *
 * Input (data seed):
 *   - Order 1: status='Selesai', total_amount=125.000
 *   - Order 2: status='Selesai', total_amount=25.000
 *   - Ekspektasi total: Rp 150.000
 *
 * Expected Result:
 *   - assertSee('Rp 150.000') — penjumlahan 125.000 + 25.000 akurat.
 */
#[Group('analytics')]
#[Group('TC-ANL-004')]
class TC_ANL_004Test extends AnalyticsTestCase
{
    #[Test]
    public function test_akurasi_kalkulasi_pendapatan_kotor_dari_beberapa_pesanan(): void
    {
        // ── Seed: Seller Gross + toko ──
        $user   = $this->createSellerUser('Seller Gross', 'seller_gross@analytics.test');
        $seller = $this->createSellerStore($user);

        // ── Seed: Order 1 — Selesai, total 125.000 ──
        $this->createOrder($seller, 'Selesai', 125000);

        // ── Seed: Order 2 — Selesai, total 25.000 ──
        $this->createOrder($seller, 'Selesai', 25000);

        // Ekspektasi: 125.000 + 25.000 = 150.000

        $this->browse(function (Browser $browser) use ($user) {
            $browser
                // ── Step 4: Login sebagai Seller Gross ──
                // Email: seller_gross@analytics.test | Password: password
                ->loginAs($user)

                // ── Step 5: Kunjungi dashboard analitik ──
                ->visitRoute('seller.analytics')

                // ── Assert: Total pendapatan = Rp 150.000 (125.000 + 25.000) ──
                ->assertSee('Rp 150.000');
        });
    }
}
