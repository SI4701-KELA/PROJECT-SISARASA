<?php

namespace Tests\Browser;

use App\Models\Complaint;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-CMP-005 — Admin Membuka Modal Tiket, Mengisi Balasan, dan Mengubah Status ke "Selesai"
 *
 * Memverifikasi Admin dapat membuka modal tiket komplain, mengetik balasan
 * di textarea balasan_admin, mengubah status_tiket menjadi 'Selesai',
 * dan menyimpan perubahan dengan sukses.
 *
 * Precondition:
 *   - Admin terdaftar dan login.
 *   - Terdapat 1 record complaints berstatus 'Open' (di-seed langsung ke DB).
 *   - Halaman admin.complaints.index dapat diakses.
 *
 * Input:
 *   - balasan_admin : "Dana akan di-refund sepenuhnya, mohon maaf atas ketidaknyamanan ini."
 *   - status_tiket  : "Selesai"
 *
 * Expected:
 *   - Redirect ke /admin/complaints.
 *   - assertSee('berhasil diperbarui').
 *   - assertDatabaseHas: id complaint, status_tiket='Selesai'.
 */
#[Group('complaint')]
#[Group('TC-CMP-005')]
class TC_CMP_005Test extends ComplaintTestCase
{
    #[Test]
    public function test_admin_membalas_dan_mengubah_status_tiket_menjadi_selesai(): void
    {
        $buyerA = $this->createBuyer('Buyer A', 'buyera5@complaint.test');
        $admin  = $this->createAdmin();
        $seller = $this->createSellerAndStore();

        // Seed tiket komplain langsung ke DB dengan status 'Open' (eskalasi ke Admin)
        $complaint = Complaint::create([
            'buyer_id'         => $buyerA->id,
            'seller_id'        => $seller->id,
            'kategori_masalah' => 'Pesanan Tidak Sesuai',
            'deskripsi'        => 'Pesanan tidak sesuai dengan apa yang saya pesan sebelumnya.',
            'status_tiket'     => 'Open',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $complaint) {
            $this->loginAs($browser, $admin)
                ->visitRoute('admin.complaints.index')

                // ── Step 1: Tunggu ID tiket muncul di daftar ──
                ->waitForText('#' . $complaint->id)

                // ── Step 2: Klik tombol Tinjau untuk membuka modal ──
                ->press('Tinjau')
                ->pause(1500) // Tunggu animasi modal Alpine.js terbuka

                // ── Step 3: Tunggu konten modal ter-render ──
                ->waitForText('PEMBELI')
                ->waitFor('textarea[name="balasan_admin"]', 10)

                // ── Step 4: Scroll textarea ke viewport ──
                ->scrollIntoView('textarea[name="balasan_admin"]')
                ->pause(800);

            // ── Step 5: Isi textarea balasan_admin via JavaScript ──
            // (script() tidak bisa di-chain, harus break di sini)
            $browser->script(
                "document.querySelector('textarea[name=\"balasan_admin\"]').focus();" .
                "document.querySelector('textarea[name=\"balasan_admin\"]').value = " .
                "'Dana akan di-refund sepenuhnya, mohon maaf atas ketidaknyamanan ini.';"
            );

            $browser
                // ── Step 6: Ubah status tiket menjadi Selesai ──
                ->select('status_tiket', 'Selesai')
                ->pause(500)

                // ── Step 7: Klik simpan ──
                ->press('Simpan & Perbarui')
                ->pause(2000) // Tunggu redirect selesai

                // ── Assert UI: Redirect ke daftar komplain admin ──
                ->assertPathIs('/admin/complaints')
                ->assertSee('berhasil diperbarui');
        });

        // ── Assert DB: Status tiket berubah menjadi Selesai ──
        $this->assertDatabaseHas('complaints', [
            'id'           => $complaint->id,
            'status_tiket' => 'Selesai',
        ]);
    }
}
