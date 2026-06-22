<?php

namespace Tests\Browser;

use App\Models\Message;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-CHT-005 — Notifikasi Badge Pesan Belum Dibaca di Sidebar Buyer
 *
 * Memverifikasi bahwa ketika Buyer memiliki 1 pesan yang belum dibaca,
 * sidebar navigasi menampilkan badge animasi dengan angka "1"
 * di dekat menu "Inbox Chat".
 *
 * Precondition:
 *   - Buyer A terdaftar di database.
 *   - Seller terdaftar di database.
 *   - 1 pesan dari Seller ke Buyer A dengan is_read=false sudah ada di tabel messages.
 *   - Halaman buyer.menu memiliki elemen <aside> sidebar.
 *
 * Login:
 *   - Aktor  : Buyer A
 *   - URL    : /login → route(buyer.menu)
 *   - Email  : buyera@chat.test
 *   - Password: password
 *
 * Steps:
 *   1. Seed 1 pesan ke DB: sender=Seller, receiver=Buyer A,
 *      message="Pesan baru dari penjual", is_read=false.
 *   2. Login sebagai Buyer A melalui form /login.
 *   3. Kunjungi route buyer.menu (halaman yang memiliki sidebar buyer).
 *   4. Tunggu elemen <aside> sidebar ter-render.
 *   5. Periksa keberadaan teks "Inbox Chat" di sidebar.
 *   6. Periksa keberadaan elemen badge animasi (aside .animate-pulse).
 *   7. Periksa badge menampilkan angka "1".
 *
 * Input:
 *   - Message seed: sender_id=seller, receiver_id=buyerA,
 *                   message="Pesan baru dari penjual", is_read=false
 *
 * Expected Result:
 *   - assertSeeIn('aside', 'Inbox Chat')          — label menu ada di sidebar.
 *   - assertPresent('aside .animate-pulse')        — badge animasi ter-render.
 *   - assertSeeIn('aside .animate-pulse', '1')     — angka unread count = 1.
 */
#[Group('chat')]
#[Group('TC-CHT-005')]
class TC_CHT_005Test extends ChatTestCase
{
    #[Test]
    public function test_badge_notifikasi_pesan_belum_dibaca_muncul_di_sidebar(): void
    {
        $buyerA = $this->createBuyerA();
        $seller = $this->createSeller();

        // ── Step 1: Seed 1 pesan belum dibaca dari Seller ke Buyer A ──
        // is_read=false → menandakan pesan ini belum dibaca oleh Buyer A
        Message::create([
            'sender_id'   => $seller->id,
            'receiver_id' => $buyerA->id,
            'message'     => 'Pesan baru dari penjual',
            'is_read'     => false,
        ]);

        $this->browse(function (Browser $browser) use ($buyerA): void {
            // ── Step 2: Login sebagai Buyer A ──
            // Email: buyera@chat.test | Password: password
            $this->loginAs($browser, $buyerA);

            $browser
                // ── Step 3: Kunjungi halaman buyer yang memiliki sidebar ──
                ->visitRoute('buyer.menu')

                // ── Step 4: Tunggu sidebar <aside> ter-render ──
                ->waitFor('aside')

                // ── Assert 1: Label "Inbox Chat" ada di sidebar ──
                ->assertSeeIn('aside', 'Inbox Chat')

                // ── Assert 2: Badge animasi (.animate-pulse) ter-render di sidebar ──
                ->assertPresent('aside .animate-pulse')

                // ── Assert 3: Badge menampilkan angka "1" (1 pesan belum dibaca) ──
                ->assertSeeIn('aside .animate-pulse', '1');
        });
    }
}
