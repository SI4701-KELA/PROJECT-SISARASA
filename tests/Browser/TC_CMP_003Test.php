<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-CMP-003 — Validasi Server-Side: Foto Bukti Wajib untuk Kategori "Kualitas Buruk/Basi"
 *
 * Memverifikasi validasi Laravel server-side menolak submit form jika
 * foto_bukti kosong saat kategori_masalah = 'Kualitas Buruk/Basi'.
 *
 * Precondition:
 *   - Buyer A terdaftar dan login.
 *   - Buyer A punya pesanan 'selesai' di Seller X (syarat akses form).
 *   - Halaman form komplain (buyer.complaint.create) dapat diakses.
 *
 * Input:
 *   - kategori_masalah : "Kualitas Buruk/Basi"
 *   - deskripsi        : teks valid (>= 20 karakter)
 *   - foto_bukti       : (kosong — atribut required HTML5 dihapus via JS agar
 *                         validasi server-side Laravel yang terpicu, bukan browser)
 *
 * Expected:
 *   - Form GAGAL dikirim.
 *   - Muncul pesan: "Foto bukti wajib diunggah untuk kategori Kualitas Buruk/Basi."
 *   - assertDatabaseMissing: tidak ada record baru di tabel complaints.
 */
#[Group('complaint')]
#[Group('TC-CMP-003')]
class TC_CMP_003Test extends ComplaintTestCase
{
    #[Test]
    public function test_validasi_foto_bukti_wajib_untuk_kategori_basi(): void
    {
        $buyerA = $this->createBuyer('Buyer A', 'buyera3@complaint.test');
        $seller = $this->createSellerAndStore();

        // Diperlukan minimal 1 pesanan 'selesai' agar form bisa diakses
        $this->createOrder($buyerA, $seller, 'selesai');

        $this->browse(function (Browser $browser) use ($buyerA, $seller) {
            $this->loginAs($browser, $buyerA)
                ->visitRoute('buyer.complaint.create', $seller->id)

                // ── Step 1: Pilih kategori yang mensyaratkan foto ──
                ->select('kategori_masalah', 'Kualitas Buruk/Basi')

                // ── Step 2: Isi deskripsi minimal 20 karakter ──
                ->type('deskripsi', 'Kualitas makanan ini sangat buruk dan basi, mohon refund secepatnya karena ini tidak layak makan.')

                // ── Step 3: Hapus atribut 'required' HTML5 dari input foto ──
                // agar form bisa di-submit tanpa foto ke server (bypass browser validation),
                // sehingga validasi Laravel server-side yang menghasilkan pesan error.
                ->script("document.getElementById('foto_bukti').removeAttribute('required')");

            $browser
                // ── Step 4: Submit form tanpa foto ──
                ->press('Kirim Komplain')

                // ── Assert UI: Pesan error server-side muncul (timeout 10 detik) ──
                ->waitForText('Foto bukti wajib diunggah untuk kategori Kualitas Buruk/Basi', 10);
        });

        // ── Assert DB: Tidak boleh ada record baru ──
        $this->assertDatabaseMissing('complaints', [
            'buyer_id'         => $buyerA->id,
            'seller_id'        => $seller->id,
            'kategori_masalah' => 'Kualitas Buruk/Basi',
        ]);
    }
}
