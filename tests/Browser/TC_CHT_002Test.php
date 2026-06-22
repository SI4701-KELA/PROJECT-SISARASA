<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-CHT-002 — AJAX Polling Menerima Pesan Real-Time (Dual Browser)
 *
 * Memverifikasi bahwa mekanisme AJAX Polling (interval 3 detik) bekerja
 * sehingga Seller menerima pesan dari Buyer TANPA perlu me-refresh halaman.
 *
 * Test ini menggunakan DUA instance browser secara bersamaan:
 *   - $buyerBrowser  : Buyer A mengirim pesan.
 *   - $sellerBrowser : Seller standby — menunggu pesan masuk via polling.
 *
 * Precondition:
 *   - User Buyer A terdaftar di database.
 *   - User Seller terdaftar di database.
 *   - Kedua user belum pernah berkirim pesan (clean state).
 *
 * Login Browser 1 (Buyer):
 *   - Aktor  : Buyer A
 *   - URL    : /login → /chat/{seller_id}
 *   - Email  : buyera@chat.test
 *   - Password: password
 *
 * Login Browser 2 (Seller):
 *   - Aktor  : Seller
 *   - URL    : /login → /chat/{buyer_id}
 *   - Email  : seller@chat.test
 *   - Password: password
 *
 * Steps:
 *   1. [Seller Browser] Login sebagai Seller, kunjungi /chat/{buyer_id}, standby.
 *   2. [Buyer Browser]  Login sebagai Buyer A, kunjungi /chat/{seller_id}.
 *   3. [Buyer Browser]  Ketik pesan: "Halo barang surplusnya ready?" dan klik Kirim.
 *   4. [Buyer Browser]  Tunggu pesan muncul di layar Buyer sendiri (maks 10 detik).
 *   5. [Seller Browser] TANPA refresh — tunggu polling membawa pesan ke Seller
 *                       dalam waktu maks 10 detik (polling interval = 3 detik).
 *
 * Input:
 *   - chat-input (Buyer) : "Halo barang surplusnya ready?"
 *   - chat-send-btn      : (klik)
 *
 * Expected Result:
 *   - [Buyer Browser]  assertSee('Halo barang surplusnya ready?')
 *   - [Seller Browser] assertSee('Halo barang surplusnya ready?') — TANPA refresh
 */
#[Group('chat')]
#[Group('TC-CHT-002')]
class TC_CHT_002Test extends ChatTestCase
{
    #[Test]
    public function test_ajax_polling_menerima_pesan_realtime(): void
    {
        $buyer  = $this->createBuyerA();
        $seller = $this->createSeller();

        // Dua browser dibutuhkan — Dusk otomatis spawn instance kedua
        $this->browse(function (Browser $buyerBrowser, Browser $sellerBrowser) use ($buyer, $seller): void {

            // ── Step 1: Seller login & standby di halaman chat ──
            // Login : seller@chat.test | password
            // URL   : /chat/{buyer_id}
            $this->loginAs($sellerBrowser, $seller);
            $sellerBrowser
                ->visit('/chat/' . $buyer->id)
                ->waitFor('#chat-input')
                ->assertPathIs('/chat/' . $buyer->id);

            // ── Step 2: Buyer login & buka halaman chat ──
            // Login : buyera@chat.test | password
            // URL   : /chat/{seller_id}
            $this->loginAs($buyerBrowser, $buyer);
            $buyerBrowser
                ->visit('/chat/' . $seller->id)
                ->waitFor('#chat-input')

                // ── Step 3: Buyer mengetik dan mengirim pesan ──
                // Input: "Halo barang surplusnya ready?"
                ->type('#chat-input', 'Halo barang surplusnya ready?')
                ->click('#chat-send-btn')

                // ── Step 4: Verifikasi pesan tampil di layar Buyer sendiri ──
                ->waitForText('Halo barang surplusnya ready?', 10)
                ->assertSee('Halo barang surplusnya ready?');

            // ── Step 5: Verifikasi Seller menerima pesan via AJAX Polling ──
            // TANPA melakukan ->refresh() — murni polling otomatis (interval 3 detik).
            // Beri waktu maks 10 detik (setara ~2–3 siklus polling).
            $sellerBrowser
                ->waitForText('Halo barang surplusnya ready?', 10)
                ->assertSee('Halo barang surplusnya ready?');
        });
    }
}
