<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-ANL-001 — Kalkulasi Pendapatan: Hanya Menghitung Pesanan Berstatus "Selesai"
 *
 * Memverifikasi bahwa total pendapatan di dashboard analitik HANYA menghitung
 * pesanan berstatus "Selesai" dan mengabaikan pesanan "Dibatalkan".
 *
 * Precondition (dari setUp):
 *   - 1 Category 'Makanan Test' tersedia di DB.
 *   - 1 User Buyer Global (buyer_global@analytics.test) tersedia di DB.
 *   - Seller Rev terdaftar dengan toko aktif (verification_status=approved).
 *   - 2 Order di-seed ke DB:
 *       Order 1: status='Selesai',    total_amount=50.000
 *       Order 2: status='Dibatalkan', total_amount=20.000
 *
 * Login:
 *   - Aktor   : Seller Rev
 *   - Metode  : ->loginAs($user) (shortcut Dusk, tidak melalui form)
 *   - Email   : seller_rev@analytics.test
 *   - Password: password
 *
 * Steps:
 *   1. Login sebagai Seller Rev (loginAs shortcut).
 *   2. Kunjungi route seller.analytics.
 *   3. Periksa nilai pendapatan yang tampil di halaman.
 *
 * Input (data seed):
 *   - Order 1 : status='Selesai',    total_amount=50.000
 *   - Order 2 : status='Dibatalkan', total_amount=20.000
 *
 * Expected Result:
 *   - assertSee('Rp 50.000')    — hanya order Selesai yang dihitung.
 *   - assertDontSee('Rp 70.000') — total keduanya tidak dijumlahkan.
 *   - assertDontSee('Rp 20.000') — order Dibatalkan tidak masuk kalkulasi.
 */
#[Group('analytics')]
#[Group('TC-ANL-001')]
class TC_ANL_001Test extends AnalyticsTestCase
{
    #[Test]
    public function test_kalkulasi_pendapatan_hanya_menghitung_pesanan_selesai(): void
    {
        // ── Seed: Seller & Toko ──
        $user   = $this->createSellerUser('Seller Rev', 'seller_rev@analytics.test');
        $seller = $this->createSellerStore($user);

        // ── Seed: 2 Order dengan status berbeda ──
        // Order 1: SELESAI → harus masuk kalkulasi pendapatan
        $this->createOrder($seller, 'Selesai', 50000);

        // Order 2: DIBATALKAN → harus diabaikan oleh kalkulasi
        $this->createOrder($seller, 'Dibatalkan', 20000);

        $this->browse(function (Browser $browser) use ($user) {
            $browser
                // ── Step 1: Login sebagai Seller Rev ──
                // Email: seller_rev@analytics.test | Password: password
                ->loginAs($user)

                // ── Step 2: Kunjungi dashboard analitik ──
                ->visitRoute('seller.analytics')

                // ── Assert 1: Hanya order Selesai (Rp 50.000) yang terhitung ──
                ->assertSee('Rp 50.000')

                // ── Assert 2: Total gabungan (Rp 70.000) TIDAK tampil ──
                ->assertDontSee('Rp 70.000')

                // ── Assert 3: Nilai order Dibatalkan (Rp 20.000) TIDAK tampil ──
                ->assertDontSee('Rp 20.000');
        });
    }
}
