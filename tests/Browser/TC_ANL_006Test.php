<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-ANL-006 — Empty State: Dashboard Seller Baru Tanpa Data Penjualan
 *
 * Memverifikasi bahwa seller yang baru mendaftar dan belum memiliki pesanan
 * apapun akan melihat tampilan "empty state" yang sesuai di dashboard analitik:
 * judul halaman, nilai pendapatan Rp 0, total porsi 0, dan teks informasi
 * "Belum ada data penjualan".
 *
 * Precondition (dari setUp):
 *   - 1 Category 'Makanan Test' tersedia di DB.
 *   - 1 User Buyer Global (buyer_global@analytics.test) tersedia di DB.
 *   - Seller Baru terdaftar dengan toko aktif.
 *   - Seller Baru TIDAK memiliki order apapun.
 *
 * Login:
 *   - Aktor   : Seller Baru
 *   - Metode  : ->loginAs($user)
 *   - Email   : new_seller@analytics.test
 *   - Password: password
 *
 * Steps:
 *   1. Seed: Seller Baru + toko (tanpa order apapun).
 *   2. Login sebagai Seller Baru (loginAs shortcut).
 *   3. Kunjungi route seller.analytics.
 *   4. Periksa tampilan empty state di halaman.
 *
 * Input (data seed):
 *   - Seller Baru: tidak ada order, tidak ada order item.
 *
 * Expected Result:
 *   - assertSee('Analitik Penjualan')        — judul halaman ada.
 *   - assertSee('Rp 0')                      — pendapatan nol.
 *   - assertSee('0')                          — total porsi nol.
 *   - assertSee('Belum ada data penjualan')  — teks empty state muncul.
 */
#[Group('analytics')]
#[Group('TC-ANL-006')]
class TC_ANL_006Test extends AnalyticsTestCase
{
    #[Test]
    public function test_empty_state_analitik_untuk_seller_baru(): void
    {
        // ── Seed: Seller Baru + toko (tanpa order) ──
        $user = $this->createSellerUser('Seller Baru', 'new_seller@analytics.test');
        $this->createSellerStore($user);

        $this->browse(function (Browser $browser) use ($user) {
            $browser
                // ── Step 2: Login sebagai Seller Baru ──
                // Email: new_seller@analytics.test | Password: password
                ->loginAs($user)

                // ── Step 3: Kunjungi dashboard analitik ──
                ->visitRoute('seller.analytics')

                // ── Assert 1: Judul halaman tampil ──
                ->assertSee('Analitik Penjualan')

                // ── Assert 2: Pendapatan = Rp 0 (tidak ada pesanan) ──
                ->assertSee('Rp 0')

                // ── Assert 3: Total porsi = 0 ──
                ->assertSee('0')

                // ── Assert 4: Teks empty state muncul ──
                ->assertSee('Belum ada data penjualan');
        });
    }
}
