<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-CMP-002 — Blokir Akses Form Komplain via Manipulasi URL (IDOR Test)
 *
 * Memverifikasi sistem menolak Buyer A mengakses form komplain
 * menggunakan order_id milik Buyer B melalui manipulasi query string.
 *
 * Precondition:
 *   - Buyer A dan Buyer B terdaftar.
 *   - Buyer B punya pesanan 'selesai' di Seller X (order_id diketahui).
 *   - Buyer A TIDAK punya pesanan 'selesai' di Seller X.
 *   - Buyer A sedang login.
 *
 * Input:
 *   - URL: /buyer/stores/{seller_id}/complaint?order_id={milik Buyer B}
 *
 * Expected:
 *   - assertPathIsNot('/buyer/stores/{seller_id}/complaint')
 *   - Sistem redirect → Buyer A tidak berhasil masuk ke form.
 *
 * Catatan:
 *   - Blokir terjadi melalui redirect (bukan abort 403/404) karena
 *     controller mengecek kepemilikan pesanan via auth()->id().
 */
#[Group('complaint')]
#[Group('TC-CMP-002')]
class TC_CMP_002Test extends ComplaintTestCase
{
    #[Test]
    public function test_pembeli_tidak_bisa_mengakses_komplain_orang_lain(): void
    {
        $buyerA = $this->createBuyer('Buyer A', 'buyera2@complaint.test');
        $buyerB = $this->createBuyer('Buyer B', 'buyerb@complaint.test');
        $seller = $this->createSellerAndStore();

        // Hanya Buyer B yang punya pesanan 'selesai' di toko ini
        $orderB = $this->createOrder($buyerB, $seller, 'selesai');

        $this->browse(function (Browser $browser) use ($buyerA, $seller, $orderB) {
            $this->loginAs($browser, $buyerA);

            // Konstruksi URL paksa: Buyer A menyertakan order_id milik Buyer B
            $url = route('buyer.complaint.create', ['seller' => $seller->id])
                 . '?order_id=' . $orderB->id;

            $browser->visit($url);

            // Sistem harus redirect karena Buyer A tidak punya pesanan selesai di toko ini.
            // Path TIDAK BOLEH tetap di halaman form komplain.
            $browser->assertPathIsNot('/buyer/stores/' . $seller->id . '/complaint');
        });
    }
}
