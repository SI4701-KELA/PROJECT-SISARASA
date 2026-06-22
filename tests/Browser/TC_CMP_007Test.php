<?php

namespace Tests\Browser;

use App\Models\Complaint;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-CMP-007 — Pembatasan Edit: Tombol "Edit Komplain" Tidak Dirender saat Tiket "Selesai"
 *
 * Memverifikasi bahwa ketika Buyer membuka form komplain ke toko yang
 * tiketnya sudah berstatus 'Selesai', UI tidak menampilkan opsi edit.
 *
 * Precondition:
 *   - Buyer A terdaftar dan login.
 *   - Buyer A punya 1 pesanan 'selesai' di Seller X.
 *   - Terdapat 1 record complaints milik Buyer A ke Seller X berstatus 'Selesai'
 *     (di-seed langsung ke DB).
 *   - Halaman buyer.complaint.create dapat diakses.
 *
 * Input:
 *   - seller_id dari toko yang komplainnya sudah berstatus 'Selesai'.
 *
 * Expected:
 *   - assertDontSee('Edit Komplain') — tombol edit tidak dirender di UI.
 *
 * Catatan:
 *   - Skenario ini hanya menguji visibilitas UI.
 *   - Pengujian bypass via API endpoint tidak dicakup dalam TC ini.
 */
#[Group('complaint')]
#[Group('TC-CMP-007')]
class TC_CMP_007Test extends ComplaintTestCase
{
    #[Test]
    public function test_tombol_edit_komplain_tidak_muncul_jika_tiket_sudah_selesai(): void
    {
        $buyerA = $this->createBuyer('Buyer A', 'buyera7@complaint.test');
        $seller = $this->createSellerAndStore();

        // Diperlukan pesanan 'selesai' agar halaman form komplain bisa diakses
        $this->createOrder($buyerA, $seller, 'selesai');

        // Seed tiket yang sudah ditutup (Selesai) untuk toko ini
        Complaint::create([
            'buyer_id'         => $buyerA->id,
            'seller_id'        => $seller->id,
            'kategori_masalah' => 'Porsi Kurang',
            'deskripsi'        => 'Porsi kurang dari yang seharusnya diterima oleh saya.',
            'status_tiket'     => 'Selesai',
        ]);

        $this->browse(function (Browser $browser) use ($buyerA, $seller) {
            $this->loginAs($browser, $buyerA)

                // ── Step: Buka halaman form komplain ke toko yang sama ──
                ->visitRoute('buyer.complaint.create', $seller->id)

                // ── Assert: Tombol "Edit Komplain" TIDAK BOLEH dirender ──
                ->assertDontSee('Edit Komplain');
        });
    }
}
