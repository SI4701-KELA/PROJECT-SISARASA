<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-IMP-001 — Filter Tipe Produk: Impact Tracker Hanya Menghitung Item Surplus
 *
 * Memverifikasi bahwa kalkulasi Impact Tracker HANYA menghitung porsi item
 * bertipe surplus (is_surplus=true) dan MENGABAIKAN item reguler (is_surplus=false),
 * meskipun keduanya berada dalam satu order yang sama.
 *
 * Precondition (dari setUp):
 *   - 1 Category 'Makanan Test' tersedia di DB.
 *   - 1 User Buyer Global (buyer_global_impact@analytics.test) tersedia di DB.
 *   - Admin terdaftar (email unik via uniqid).
 *   - Toko Sisa Rasa terdaftar dengan verification_status=approved.
 *   - 1 Order berstatus 'Selesai' berisi 2 OrderItem:
 *       Item 1: qty=10, is_surplus=false, price=10.000 (REGULER)
 *       Item 2: qty=5,  is_surplus=true,  price=10.000 (SURPLUS)
 *
 * Login:
 *   - Aktor   : Admin
 *   - Metode  : ->loginAs($admin) (shortcut Dusk)
 *   - Email   : admin_impact_{uniqid}@test.com
 *   - Password: password
 *
 * Steps:
 *   1. Seed: Admin + Seller + Toko.
 *   2. Seed: 1 Order Selesai.
 *   3. Seed: Item reguler qty=10 (is_surplus=false, price=10.000).
 *   4. Seed: Item surplus qty=5  (is_surplus=true,  price=10.000).
 *   5. Login sebagai Admin (loginAs shortcut).
 *   6. Kunjungi route admin.impact-tracker.
 *   7. Tunggu 1500ms untuk halaman selesai dirender.
 *   8. Periksa nilai di elemen #hero-food-saved dan #card-financial.
 *
 * Input (data seed):
 *   - OrderItem 1: qty=10, is_surplus=false, price=10.000 → diabaikan
 *   - OrderItem 2: qty=5,  is_surplus=true,  price=10.000 → dihitung
 *   - Kerugian finansial dicegah: 5 × 10.000 = 50.000
 *
 * Expected Result:
 *   - assertSeeIn('#hero-food-saved', '5')    — hanya 5 porsi surplus.
 *   - assertSeeIn('#card-financial', '50.000') — 5 × Rp 10.000.
 */
#[Group('impact')]
#[Group('TC-IMP-001')]
class TC_IMP_001Test extends ImpactTrackerTestCase
{
    #[Test]
    public function test_impact_tracker_hanya_menghitung_item_surplus(): void
    {
        // ── Seed: Admin ──
        $admin = $this->createAdminUser();

        // ── Seed: Seller & Toko ──
        $user   = $this->createSellerUser('Toko Sisa Rasa');
        $seller = $this->createSellerStore($user, 'Toko Sisa Rasa');

        // ── Seed: 1 Order Selesai ──
        $order = $this->createOrder($seller, 'Selesai');

        // ── Seed: Item 1 — REGULER, qty=10, price=10.000 (harus DIABAIKAN) ──
        $this->createOrderItem($order, $seller, 10, false, 10000);

        // ── Seed: Item 2 — SURPLUS, qty=5, price=10.000 (harus DIHITUNG) ──
        $this->createOrderItem($order, $seller, 5, true, 10000);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser
                // ── Step 5: Login sebagai Admin ──
                // Email: admin_impact_{uniqid}@test.com | Password: password
                ->loginAs($admin)

                // ── Step 6: Kunjungi halaman Impact Tracker ──
                ->visitRoute('admin.impact-tracker')

                // ── Step 7: Tunggu rendering selesai ──
                ->pause(1500)

                // ── Assert 1: Hanya 5 porsi surplus yang terhitung ──
                ->assertSeeIn('#hero-food-saved', '5')

                // ── Assert 2: Kerugian finansial = 5 × Rp 10.000 = Rp 50.000 ──
                ->assertSeeIn('#card-financial', '50.000');
        });
    }
}
