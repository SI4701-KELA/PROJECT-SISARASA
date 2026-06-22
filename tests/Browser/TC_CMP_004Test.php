<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-CMP-004 — Pengiriman Komplain Berhasil & Verifikasi Relasi Database
 *
 * Memverifikasi komplain berhasil disimpan ke database dengan relasi
 * buyer_id dan seller_id yang benar, serta status_tiket awal = 'menunggu_seller'.
 *
 * Precondition:
 *   - Buyer A terdaftar dan login.
 *   - Buyer A punya pesanan 'selesai' di Seller X.
 *   - Halaman form komplain dapat diakses.
 *
 * Input:
 *   - kategori_masalah : "Porsi Kurang"
 *   - deskripsi        : teks valid (>= 20 karakter)
 *   - foto_bukti       : (tidak diunggah — kategori ini tidak mensyaratkan foto)
 *
 * Expected:
 *   - Redirect ke /buyer/complaints.
 *   - assertSee('Komplain berhasil').
 *   - assertDatabaseHas: buyer_id, seller_id, kategori_masalah='Porsi Kurang',
 *     status_tiket='menunggu_seller'.
 */
#[Group('complaint')]
#[Group('TC-CMP-004')]
class TC_CMP_004Test extends ComplaintTestCase
{
    #[Test]
    public function test_pengiriman_komplain_berhasil_dan_relasi_database_benar(): void
    {
        $buyerA = $this->createBuyer('Buyer A', 'buyera4@complaint.test');
        $seller = $this->createSellerAndStore();

        // Diperlukan minimal 1 pesanan 'selesai' agar form bisa diakses
        $this->createOrder($buyerA, $seller, 'selesai');

        $this->browse(function (Browser $browser) use ($buyerA, $seller) {
            $this->loginAs($browser, $buyerA)
                ->visitRoute('buyer.complaint.create', $seller->id)

                // ── Step 1: Pilih kategori (bukan Kualitas Buruk/Basi → foto opsional) ──
                ->select('kategori_masalah', 'Porsi Kurang')

                // ── Step 2: Isi deskripsi minimal 20 karakter ──
                ->type('deskripsi', 'Porsi makanannya jauh berbeda dari foto, mohon segera ditindaklanjuti karena ini mengecewakan.')

                // ── Step 3: Submit tanpa foto ──
                ->press('Kirim Komplain')

                // ── Assert UI: Redirect ke halaman Pusat Bantuan Buyer ──
                ->waitForLocation('/buyer/complaints')
                ->assertSee('Komplain berhasil');
        });

        // ── Assert DB: Record tersimpan dengan relasi dan status awal yang benar ──
        $this->assertDatabaseHas('complaints', [
            'buyer_id'         => $buyerA->id,
            'seller_id'        => $seller->id,
            'kategori_masalah' => 'Porsi Kurang',
            'status_tiket'     => 'menunggu_seller',
        ]);
    }
}
