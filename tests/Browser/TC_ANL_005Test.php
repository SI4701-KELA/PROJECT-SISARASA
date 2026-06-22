<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-ANL-005 — Render UI: Kartu Statistik dan Grafik Sales Terpasang di DOM
 *
 * Memverifikasi bahwa semua elemen UI utama dashboard analitik —
 * yaitu tiga kartu statistik (#card-pendapatan, #card-surplus, #card-reguler)
 * dan grafik penjualan (#salesChart) — hadir (present) di dalam DOM
 * setelah halaman dimuat dengan data yang ada.
 *
 * Precondition (dari setUp):
 *   - 1 Category 'Makanan Test' tersedia di DB.
 *   - 1 User Buyer Global (buyer_global@analytics.test) tersedia di DB.
 *   - Seller UI terdaftar dengan toko aktif.
 *   - 1 Order berstatus 'Selesai' total_amount=10.000 di-seed.
 *   - 1 OrderItem reguler (qty=1, is_surplus=false) di-seed ke order tersebut.
 *
 * Login:
 *   - Aktor   : Seller UI
 *   - Metode  : ->loginAs($user)
 *   - Email   : seller_ui@analytics.test
 *   - Password: password
 *
 * Steps:
 *   1. Seed: Seller UI + toko + 1 order Selesai + 1 order item reguler.
 *   2. Login sebagai Seller UI (loginAs shortcut).
 *   3. Kunjungi route seller.analytics.
 *   4. Periksa keberadaan elemen-elemen UI berikut di DOM:
 *      - #card-pendapatan
 *      - #card-surplus
 *      - #card-reguler
 *      - #salesChart
 *
 * Input (data seed):
 *   - Order: status='Selesai', total_amount=10.000
 *   - OrderItem: qty=1, is_surplus=false, price=10.000
 *
 * Expected Result:
 *   - assertPresent('#card-pendapatan') — kartu pendapatan dirender.
 *   - assertPresent('#card-surplus')   — kartu surplus dirender.
 *   - assertPresent('#card-reguler')   — kartu reguler dirender.
 *   - assertPresent('#salesChart')     — canvas/elemen grafik dirender.
 */
#[Group('analytics')]
#[Group('TC-ANL-005')]
class TC_ANL_005Test extends AnalyticsTestCase
{
    #[Test]
    public function test_ui_merender_kartu_statistik_dan_grafik(): void
    {
        // ── Seed: Seller UI + toko ──
        $user   = $this->createSellerUser('Seller UI', 'seller_ui@analytics.test');
        $seller = $this->createSellerStore($user);

        // ── Seed: 1 Order Selesai ──
        $order = $this->createOrder($seller, 'Selesai', 10000);

        // ── Seed: 1 OrderItem reguler (qty=1, is_surplus=false) ──
        $this->createOrderItem($order, $seller, 1, false);

        $this->browse(function (Browser $browser) use ($user) {
            $browser
                // ── Step 2: Login sebagai Seller UI ──
                // Email: seller_ui@analytics.test | Password: password
                ->loginAs($user)

                // ── Step 3: Kunjungi dashboard analitik ──
                ->visitRoute('seller.analytics')

                // ── Assert 1: Kartu pendapatan ada di DOM ──
                ->assertPresent('#card-pendapatan')

                // ── Assert 2: Kartu surplus ada di DOM ──
                ->assertPresent('#card-surplus')

                // ── Assert 3: Kartu reguler ada di DOM ──
                ->assertPresent('#card-reguler')

                // ── Assert 4: Elemen grafik (#salesChart) ada di DOM ──
                ->assertPresent('#salesChart');
        });
    }
}
