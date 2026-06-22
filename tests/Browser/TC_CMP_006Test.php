<?php

namespace Tests\Browser;

use App\Models\Complaint;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-CMP-006 — Buyer Membaca Status Terkini dan Balasan Admin di Pusat Bantuan
 *
 * Memverifikasi Buyer dapat melihat status_tiket = 'Selesai' dan teks
 * balasan Admin di halaman daftar riwayat komplain (buyer.complaints.index).
 *
 * Test ini INDEPENDEN dari TC-CMP-005 — data di-seed langsung ke DB
 * tanpa bergantung pada jalannya test sebelumnya.
 *
 * Precondition:
 *   - Buyer A terdaftar dan login.
 *   - Terdapat 1 record complaints milik Buyer A dengan:
 *       status_tiket  = 'Selesai'
 *       balasan_admin = 'Dana di-refund sepenuhnya.'
 *     (di-seed langsung ke DB).
 *   - Halaman buyer.complaints.index dapat diakses.
 *
 * Input:
 *   - complaint_id tiket yang sudah berstatus 'Selesai' dan ada balasan_admin.
 *
 * Expected:
 *   - assertSee('Dana di-refund sepenuhnya.')
 *   - assertSee('Selesai')
 */
#[Group('complaint')]
#[Group('TC-CMP-006')]
class TC_CMP_006Test extends ComplaintTestCase
{
    #[Test]
    public function test_buyer_membaca_balasan_admin_di_pusat_bantuan(): void
    {
        $buyerA = $this->createBuyer('Buyer A', 'buyera6@complaint.test');
        $seller = $this->createSellerAndStore();

        // Seed tiket yang sudah dibalas Admin — independen dari TC-CMP-005
        $complaint = Complaint::create([
            'buyer_id'         => $buyerA->id,
            'seller_id'        => $seller->id,
            'kategori_masalah' => 'Pesanan Tidak Sesuai',
            'deskripsi'        => 'Pesanan tidak sesuai.',
            'balasan_admin'    => 'Dana di-refund sepenuhnya.',
            'status_tiket'     => 'Selesai',
        ]);

        $this->browse(function (Browser $browser) use ($buyerA, $complaint) {
            $this->loginAs($browser, $buyerA)

                // ── Step 1: Buka halaman Pusat Bantuan versi Buyer ──
                ->visitRoute('buyer.complaints.index')

                // ── Step 2: Tunggu ID tiket muncul di halaman ──
                ->waitForText('#' . $complaint->id)

                // ── Assert 1: Teks balasan Admin terlihat ──
                ->assertSee('Dana di-refund sepenuhnya.')

                // ── Assert 2: Label status "Selesai" terlihat ──
                ->assertSee('Selesai');
        });
    }
}
