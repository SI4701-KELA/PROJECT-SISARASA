<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-IMP-007 — Visualisasi Makro & Tipografi UI: Elemen Visual Ter-render dengan Benar
 *
 * Memverifikasi bahwa semua elemen visual utama halaman Impact Tracker
 * — badge ranking (.rank-badge) dan label kartu statistik — hadir dan
 * ter-render dengan teks konten yang benar setelah halaman dimuat.
 *
 * Precondition (dari setUp):
 *   - 1 Category 'Makanan Test' tersedia di DB.
 *   - 1 User Buyer Global (buyer_global_impact@analytics.test) tersedia di DB.
 *   - Admin terdaftar (email unik via uniqid).
 *   - Toko Visual terdaftar dengan verification_status=approved.
 *   - 1 Order berstatus 'Selesai' berisi 1 item surplus qty=1 di-seed.
 *
 * Login:
 *   - Aktor   : Admin
 *   - Metode  : ->loginAs($admin)
 *   - Email   : admin_impact_{uniqid}@test.com
 *   - Password: password
 *
 * Steps:
 *   1. Seed: Admin + Seller + Toko Visual.
 *   2. Seed: 1 Order Selesai + 1 item surplus qty=1.
 *   3. Login sebagai Admin.
 *   4. Kunjungi route admin.impact-tracker.
 *   5. Tunggu 1500ms untuk semua elemen selesai dirender.
 *   6. Periksa keberadaan elemen badge CSS (.rank-badge).
 *   7. Periksa keberadaan teks label kartu statistik.
 *
 * Input (data seed):
 *   - Order Selesai: 1 item surplus qty=1
 *
 * Expected Result:
 *   - assertPresent('.rank-badge')                         — badge ter-render di DOM.
 *   - assertSee('Porsi Makanan Berhasil Diselamatkan')     — label kartu food-saved.
 *   - assertSee('Kerugian Finansial Dicegah')              — label kartu finansial.
 *   - assertSee('UMKM Berkontribusi Aktif')                — label kartu UMKM.
 */
#[Group('impact')]
#[Group('TC-IMP-007')]
class TC_IMP_007Test extends ImpactTrackerTestCase
{
    #[Test]
    public function test_visualisasi_makro_dan_tipografi_ui_terrender_dengan_benar(): void
    {
        // ── Seed: Admin ──
        $admin = $this->createAdminUser();

        // ── Seed: Seller & Toko Visual ──
        $seller = $this->createSellerStore($this->createSellerUser('Toko Visual'), 'Toko Visual');

        // ── Seed: 1 Order Selesai + 1 item surplus ──
        $order = $this->createOrder($seller, 'Selesai');
        $this->createOrderItem($order, $seller, 1, true);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser
                // ── Step 3: Login sebagai Admin ──
                // Email: admin_impact_{uniqid}@test.com | Password: password
                ->loginAs($admin)

                // ── Step 4: Kunjungi halaman Impact Tracker ──
                ->visitRoute('admin.impact-tracker')

                // ── Step 5: Tunggu semua elemen selesai dirender ──
                ->pause(1500)

                // ── Assert 1: Badge ranking ter-render di DOM ──
                ->assertPresent('.rank-badge')

                // ── Assert 2: Label kartu "Porsi Makanan Berhasil Diselamatkan" ada ──
                ->assertSee('Porsi Makanan Berhasil Diselamatkan')

                // ── Assert 3: Label kartu "Kerugian Finansial Dicegah" ada ──
                ->assertSee('Kerugian Finansial Dicegah')

                // ── Assert 4: Label kartu "UMKM Berkontribusi Aktif" ada ──
                ->assertSee('UMKM Berkontribusi Aktif');
        });
    }
}
