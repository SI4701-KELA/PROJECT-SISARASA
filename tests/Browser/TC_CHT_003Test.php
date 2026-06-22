<?php

namespace Tests\Browser;

use App\Models\Message;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-CHT-003 — Isolasi Privasi: Buyer Tidak Dapat Mengakses Chat Buyer Lain
 *
 * Memverifikasi bahwa Buyer B tidak dapat mengintip riwayat chat
 * antara Buyer A dan Seller dengan mengakses URL /chat/{buyer_a_id}.
 * Sistem harus mengembalikan halaman error 403 (Forbidden).
 *
 * Desain Authorization:
 *   - Pair valid  : Buyer ↔ Seller  (diizinkan)
 *   - Pair invalid: Buyer ↔ Buyer   (diblokir — abort 403 via authorizeChat())
 *
 * Precondition:
 *   - Buyer A terdaftar di database.
 *   - Buyer B terdaftar di database.
 *   - Seller terdaftar di database.
 *   - 1 pesan dari Buyer A ke Seller sudah ada di tabel messages (riwayat).
 *
 * Login:
 *   - Aktor  : Buyer B
 *   - URL    : /login → /chat/{buyer_a_id}
 *   - Email  : buyerb@chat.test
 *   - Password: password
 *
 * Steps:
 *   1. Seed 1 pesan ke DB: sender=Buyer A, receiver=Seller,
 *      message="Pesan rahasia dari Buyer A".
 *   2. Login sebagai Buyer B melalui form /login.
 *   3. Kunjungi /chat/{buyer_a_id} secara langsung (paksa via URL).
 *   4. Tunggu teks "403" muncul di halaman (maks 10 detik).
 *   5. Periksa bahwa halaman menampilkan kode error 403.
 *   6. Verifikasi Buyer B tidak berada di URL success chat.
 *
 * Input:
 *   - URL target   : /chat/{buyer_a_id}  (Buyer B mencoba akses chat milik Buyer A)
 *   - Message seed : sender_id=buyerA, receiver_id=seller, message="Pesan rahasia dari Buyer A"
 *
 * Expected Result:
 *   - assertSee('403')                             — halaman 403 ditampilkan.
 *   - assertPathIsNot('/chat/{buyer_a_id}/success') — tidak berhasil masuk ke chat.
 */
#[Group('chat')]
#[Group('TC-CHT-003')]
class TC_CHT_003Test extends ChatTestCase
{
    #[Test]
    public function test_pengguna_lain_tidak_bisa_mengakses_chat_buyer(): void
    {
        $buyerA = $this->createBuyerA();
        $buyerB = $this->createBuyerB();
        $seller = $this->createSeller();

        // ── Step 1: Seed riwayat chat Buyer A ↔ Seller ──
        // Pesan: "Pesan rahasia dari Buyer A"
        Message::create([
            'sender_id'   => $buyerA->id,
            'receiver_id' => $seller->id,
            'message'     => 'Pesan rahasia dari Buyer A',
        ]);

        $this->browse(function (Browser $browser) use ($buyerA, $buyerB): void {
            // ── Step 2: Login sebagai Buyer B ──
            // Email: buyerb@chat.test | Password: password
            $this->loginAs($browser, $buyerB);

            // ── Step 3: Buyer B paksa akses /chat/{buyer_a_id} (Buyer ↔ Buyer) ──
            $browser->visit('/chat/' . $buyerA->id);

            // ── Step 4 & Assert 1: Halaman harus menampilkan error 403 ──
            $browser
                ->waitForText('403', 10)
                ->assertSee('403');

            // ── Assert 2: Buyer B tidak berhasil masuk ke area chat ──
            $browser->assertPathIsNot('/chat/' . $buyerA->id . '/success');
        });
    }
}
