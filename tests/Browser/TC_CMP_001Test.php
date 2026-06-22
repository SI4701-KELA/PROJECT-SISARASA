<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-CMP-001 — Visibilitas Tombol "Ajukan Komplain" Berdasarkan Status Pesanan
 *
 * Memverifikasi tombol HANYA muncul di halaman detail pesanan berstatus 'selesai'
 * dan TIDAK muncul pada pesanan berstatus 'dibatalkan'.
 *
 * Precondition:
 *   - Buyer A terdaftar dan login.
 *   - Terdapat 1 pesanan status='selesai' milik Buyer A.
 *   - Terdapat 1 pesanan status='dibatalkan' milik Buyer A.
 *
 * Input:
 *   - order_id pesanan 'selesai'
 *   - order_id pesanan 'dibatalkan'
 *
 * Expected:
 *   - assertSee('Ajukan Komplain') pada pesanan selesai.
 *   - assertDontSee('Ajukan Komplain') pada pesanan dibatalkan.
 */
#[Group('complaint')]
#[Group('TC-CMP-001')]
class TC_CMP_001Test extends ComplaintTestCase
{
    #[Test]
    public function test_tombol_komplain_muncul_berdasarkan_status(): void
    {
        $buyerA = $this->createBuyer('Buyer A', 'buyera@complaint.test');
        $seller = $this->createSellerAndStore();

        $orderSelesai    = $this->createOrder($buyerA, $seller, 'selesai');
        $orderDibatalkan = $this->createOrder($buyerA, $seller, 'dibatalkan');

        $this->browse(function (Browser $browser) use ($buyerA, $orderSelesai, $orderDibatalkan) {
            $this->loginAs($browser, $buyerA)

                // ── Assert 1: Pesanan SELESAI → tombol HARUS ada ──
                ->visitRoute('buyer.orders.show', $orderSelesai->id)
                ->waitForText('NOTA PEMBAYARAN RESMI')
                ->assertSee('Ajukan Komplain')

                // ── Assert 2: Pesanan DIBATALKAN → tombol TIDAK BOLEH ada ──
                ->visitRoute('buyer.orders.show', $orderDibatalkan->id)
                ->waitForText('NOTA PEMBAYARAN RESMI')
                ->assertDontSee('Ajukan Komplain');
        });
    }
}
