<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-CHT-001 — Sifat Permanen Chat: Pesan Tidak Dapat Diedit atau Dihapus
 *
 * Memverifikasi bahwa sistem chat bersifat Create-Read ONLY.
 * Setelah pesan dikirim, tidak ada tombol "Edit" atau "Hapus" yang dirender di UI.
 *
 * Precondition:
 *   - User Buyer A terdaftar di database.
 *   - User Seller terdaftar di database.
 *   - Buyer A belum pernah berkirim pesan dengan Seller (clean state).
 *
 * Login:
 *   - Aktor  : Buyer A
 *   - URL    : /login
 *   - Email  : buyera@chat.test
 *   - Password: password
 *
 * Steps:
 *   1. Login sebagai Buyer A melalui form /login.
 *   2. Kunjungi halaman chat dengan Seller: /chat/{seller_id}.
 *   3. Tunggu elemen input chat (#chat-input) muncul.
 *   4. Ketik pesan: "Apakah stok masih ada?".
 *   5. Klik tombol kirim (#chat-send-btn).
 *   6. Tunggu teks pesan muncul di DOM (maks 10 detik — AJAX append).
 *
 * Input:
 *   - chat-input  : "Apakah stok masih ada?"
 *   - chat-send-btn: (klik)
 *
 * Expected Result:
 *   - assertSee('Apakah stok masih ada?') — pesan tampil di layar.
 *   - assertDontSee('Hapus') — tidak ada tombol hapus.
 *   - assertDontSee('Edit')  — tidak ada tombol edit.
 */
#[Group('chat')]
#[Group('TC-CHT-001')]
class TC_CHT_001Test extends ChatTestCase
{
    #[Test]
    public function test_pesan_permanen_dan_tidak_bisa_diedit(): void
    {
        $buyer  = $this->createBuyerA();
        $seller = $this->createSeller();

        $this->browse(function (Browser $browser) use ($buyer, $seller): void {
            // ── Step 1–2: Login Buyer A & kunjungi halaman chat ──
            // URL   : /login → /chat/{seller_id}
            // Email : buyera@chat.test | Password: password
            $this->loginAs($browser, $buyer);

            $browser
                ->visit('/chat/' . $seller->id)

                // ── Step 3: Tunggu input chat siap ──
                ->waitFor('#chat-input')

                // ── Step 4–5: Ketik pesan dan klik Kirim ──
                // Input: "Apakah stok masih ada?"
                ->type('#chat-input', 'Apakah stok masih ada?')
                ->click('#chat-send-btn')

                // ── Step 6: Tunggu pesan muncul via AJAX (maks 10 detik) ──
                ->waitForText('Apakah stok masih ada?', 10)

                // ── Assert 1: Pesan tampil di layar ──
                ->assertSee('Apakah stok masih ada?')

                // ── Assert 2 & 3: Tidak ada UI edit/hapus (CR-only) ──
                ->assertDontSee('Hapus')
                ->assertDontSee('Edit');
        });
    }
}
