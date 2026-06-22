<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-IMP-006 — Pembaruan Data Setelah Pesanan Baru Selesai (Page Refresh)
 *
 * Memverifikasi bahwa data Impact Tracker diperbarui secara akurat setelah
 * ada pesanan baru yang berubah menjadi 'Selesai' dan halaman di-refresh.
 *
 * Skenario: Admin membuka halaman (total awal = 1 porsi), kemudian backend
 * menambahkan pesanan baru langsung ke DB (simulasi), lalu halaman di-refresh.
 * Total baru harus menjadi 1 + 5 = 6 porsi.
 *
 * Precondition (dari setUp):
 *   - 1 Category 'Makanan Test' tersedia di DB.
 *   - 1 User Buyer Global (buyer_global_impact@analytics.test) tersedia di DB.
 *   - Admin terdaftar (email unik via uniqid).
 *   - Toko Realtime terdaftar dengan verification_status=approved.
 *   - 1 Order Selesai dengan 1 item surplus qty=1 di-seed SEBELUM browser dibuka.
 *
 * Login:
 *   - Aktor   : Admin
 *   - Metode  : ->loginAs($admin)
 *   - Email   : admin_impact_{uniqid}@test.com
 *   - Password: password
 *
 * Steps:
 *   1. Seed: Admin + Seller + Toko Realtime.
 *   2. Seed: Order 1 Selesai + item surplus qty=1 (state awal).
 *   3. Login sebagai Admin.
 *   4. Kunjungi route admin.impact-tracker.
 *   5. Tunggu 1500ms — periksa nilai awal di #hero-food-saved.
 *   6. [Simulasi Backend] Buat Order 2 Selesai + item surplus qty=5 langsung ke DB.
 *   7. Panggil ->refresh() pada browser.
 *   8. Tunggu 1500ms — periksa nilai setelah refresh.
 *
 * Input (data seed):
 *   - Order 1 (pre-seed) : qty=1 surplus → tampil sebelum refresh
 *   - Order 2 (simulasi) : qty=5 surplus → ditambah setelah halaman dibuka
 *   - Ekspektasi setelah refresh: 1 + 5 = 6
 *
 * Expected Result:
 *   - Sebelum refresh: assertSeeIn('#hero-food-saved', '1')
 *   - Setelah refresh: assertSeeIn('#hero-food-saved', '6')
 */
#[Group('impact')]
#[Group('TC-IMP-006')]
class TC_IMP_006Test extends ImpactTrackerTestCase
{
    #[Test]
    public function test_update_data_realtime_saat_pesanan_baru_selesai(): void
    {
        // ── Seed: Admin ──
        $admin = $this->createAdminUser();

        // ── Seed: Seller & Toko Realtime ──
        $seller = $this->createSellerStore($this->createSellerUser('Toko Realtime'), 'Toko Realtime');

        // ── Seed: Order 1 Selesai — kondisi awal (qty=1 surplus) ──
        $order1 = $this->createOrder($seller, 'Selesai');
        $this->createOrderItem($order1, $seller, 1, true);

        $this->browse(function (Browser $browser) use ($admin, $seller) {
            $browser
                // ── Step 3: Login sebagai Admin ──
                // Email: admin_impact_{uniqid}@test.com | Password: password
                ->loginAs($admin)

                // ── Step 4: Kunjungi halaman Impact Tracker ──
                ->visitRoute('admin.impact-tracker')

                // ── Step 5: Tunggu & periksa nilai awal ──
                ->pause(1500);

            // ── Assert SEBELUM refresh: 1 porsi ──
            $browser->assertSeeIn('#hero-food-saved', '1');

            // ── Step 6: Simulasi Backend — tambah Order 2 Selesai (qty=5 surplus) ──
            // Tidak melalui UI — langsung ke DB (seperti proses penyelesaian order di backend)
            $order2 = $this->createOrder($seller, 'Selesai');
            $this->createOrderItem($order2, $seller, 5, true);

            // ── Step 7 & 8: Refresh halaman, tunggu, periksa nilai baru ──
            $browser
                ->refresh()
                ->pause(1500);

            // ── Assert SETELAH refresh: 1 + 5 = 6 porsi ──
            $browser->assertSeeIn('#hero-food-saved', '6');
        });
    }
}
