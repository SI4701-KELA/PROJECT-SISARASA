<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-IMP-003 — Akurasi Jumlah Porsi Makanan: Penjumlahan qty dari Beberapa Item
 *
 * Memverifikasi bahwa sistem menjumlahkan kolom `qty` dari beberapa
 * OrderItem surplus dalam satu pesanan secara akurat (3 + 4 = 7).
 *
 * Precondition (dari setUp):
 *   - 1 Category 'Makanan Test' tersedia di DB.
 *   - 1 User Buyer Global (buyer_global_impact@analytics.test) tersedia di DB.
 *   - Admin terdaftar (email unik via uniqid).
 *   - Toko QTY terdaftar dengan verification_status=approved.
 *   - 1 Order berstatus 'Selesai' berisi 2 OrderItem surplus:
 *       Item 1: qty=3, is_surplus=true, price=10.000 (default)
 *       Item 2: qty=4, is_surplus=true, price=10.000 (default)
 *
 * Login:
 *   - Aktor   : Admin
 *   - Metode  : ->loginAs($admin)
 *   - Email   : admin_impact_{uniqid}@test.com
 *   - Password: password
 *
 * Steps:
 *   1. Seed: Admin + Seller + Toko QTY.
 *   2. Seed: 1 Order Selesai.
 *   3. Seed: Item surplus 1, qty=3, price=10.000.
 *   4. Seed: Item surplus 2, qty=4, price=10.000.
 *   5. Login sebagai Admin.
 *   6. Kunjungi route admin.impact-tracker.
 *   7. Tunggu 1500ms.
 *   8. Periksa nilai di #hero-food-saved.
 *
 * Input (data seed):
 *   - OrderItem 1: qty=3, is_surplus=true
 *   - OrderItem 2: qty=4, is_surplus=true
 *   - Ekspektasi total porsi: 3 + 4 = 7
 *
 * Expected Result:
 *   - assertSeeIn('#hero-food-saved', '7') — hasil penjumlahan 3 + 4 akurat.
 */
#[Group('impact')]
#[Group('TC-IMP-003')]
class TC_IMP_003Test extends ImpactTrackerTestCase
{
    #[Test]
    public function test_akurasi_jumlah_porsi_makanan_dari_beberapa_item(): void
    {
        // ── Seed: Admin ──
        $admin = $this->createAdminUser();

        // ── Seed: Seller & Toko QTY ──
        $seller = $this->createSellerStore($this->createSellerUser('Toko QTY'), 'Toko QTY');

        // ── Seed: 1 Order Selesai ──
        $order = $this->createOrder($seller, 'Selesai');

        // ── Seed: Item surplus 1 — qty=3 ──
        $this->createOrderItem($order, $seller, 3, true);

        // ── Seed: Item surplus 2 — qty=4 ──
        $this->createOrderItem($order, $seller, 4, true);

        // Ekspektasi total: 3 + 4 = 7

        $this->browse(function (Browser $browser) use ($admin) {
            $browser
                // ── Step 5: Login sebagai Admin ──
                // Email: admin_impact_{uniqid}@test.com | Password: password
                ->loginAs($admin)

                // ── Step 6: Kunjungi halaman Impact Tracker ──
                ->visitRoute('admin.impact-tracker')

                // ── Step 7: Tunggu rendering selesai ──
                ->pause(1500)

                // ── Assert: Total porsi = 7 (3 + 4) tampil akurat ──
                ->assertSeeIn('#hero-food-saved', '7');
        });
    }
}
