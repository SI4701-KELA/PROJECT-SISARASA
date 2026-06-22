<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-ANL-007 — Filter Waktu: Klik Filter "Hari Ini" Mengubah Query String
 *
 * Memverifikasi bahwa ketika Seller mengklik tombol filter waktu
 * (#filter-today) di dashboard analitik, halaman memperbarui query string
 * URL dengan parameter filter=today tanpa perlu reload manual.
 *
 * Precondition (dari setUp):
 *   - 1 Category 'Makanan Test' tersedia di DB.
 *   - 1 User Buyer Global (buyer_global@analytics.test) tersedia di DB.
 *   - Seller Filter terdaftar dengan toko aktif.
 *   - Seller Filter TIDAK memiliki order (filter tetap bisa diklik).
 *
 * Login:
 *   - Aktor   : Seller Filter
 *   - Metode  : ->loginAs($user)
 *   - Email   : seller_filter@analytics.test
 *   - Password: password
 *
 * Steps:
 *   1. Seed: Seller Filter + toko (tanpa order).
 *   2. Login sebagai Seller Filter (loginAs shortcut).
 *   3. Kunjungi route seller.analytics.
 *   4. Klik elemen tombol filter #filter-today.
 *   5. Tunggu 1000ms untuk navigasi/request selesai.
 *   6. Periksa query string URL yang aktif.
 *
 * Input:
 *   - Klik elemen : #filter-today
 *   - Pause       : 1000ms (tunggu request/navigasi)
 *
 * Expected Result:
 *   - assertQueryStringHas('filter', 'today') — URL mengandung ?filter=today.
 */
#[Group('analytics')]
#[Group('TC-ANL-007')]
class TC_ANL_007Test extends AnalyticsTestCase
{
    #[Test]
    public function test_klik_filter_hari_ini_mengubah_query_string(): void
    {
        // ── Seed: Seller Filter + toko ──
        $user = $this->createSellerUser('Seller Filter', 'seller_filter@analytics.test');
        $this->createSellerStore($user);

        $this->browse(function (Browser $browser) use ($user) {
            $browser
                // ── Step 2: Login sebagai Seller Filter ──
                // Email: seller_filter@analytics.test | Password: password
                ->loginAs($user)

                // ── Step 3: Kunjungi dashboard analitik ──
                ->visitRoute('seller.analytics')

                // ── Step 4: Klik tombol filter "Hari Ini" ──
                ->click('#filter-today')

                // ── Step 5: Tunggu navigasi/request selesai (1 detik) ──
                ->pause(1000)

                // ── Assert: URL query string mengandung filter=today ──
                ->assertQueryStringHas('filter', 'today');
        });
    }
}
