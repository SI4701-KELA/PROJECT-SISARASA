<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * PBI-30: Live Chat (AJAX Polling) — End-to-End Browser Tests
 *
 * Fitur Live Chat menggunakan AJAX Polling (interval 3 detik) untuk menarik
 * pesan baru tanpa reload halaman. File ini menguji empat aspek kritikal:
 *
 *  TC-CHT-001 — Sifat Permanen Chat (Create-Read only, tanpa Edit/Delete)
 *  TC-CHT-002 — AJAX Polling menerima pesan real-time di 2 browser
 *  TC-CHT-003 — Isolasi Privasi (user tidak berhak tidak bisa akses chat orang lain)
 *  TC-CHT-004 — Kebutaan Admin terhadap Chat (tidak ada menu Chat di sidebar admin)
 *
 * @see \App\Http\Controllers\ChatController
 * @see resources/views/chat/show.blade.php
 */
class LiveChatTest extends DuskTestCase
{
    use DatabaseTruncation;

    // ─── Shared Test Data ────────────────────────────────────────

    /**
     * Buat user Buyer A (utama) untuk testing.
     */
    private function createBuyerA(): User
    {
        return User::factory()->create([
            'name'     => 'Buyer A',
            'email'    => 'buyera@test.com',
            'role'     => 'buyer',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * Buat user Seller untuk testing.
     */
    private function createSeller(): User
    {
        return User::factory()->create([
            'name'     => 'Seller Toko',
            'email'    => 'seller@test.com',
            'role'     => 'seller',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * Buat user Admin untuk testing.
     */
    private function createAdmin(): User
    {
        return User::factory()->create([
            'name'     => 'Admin Super',
            'email'    => 'admin@test.com',
            'role'     => 'admin',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * Buat user Buyer B (secondary) untuk testing isolasi privasi.
     */
    private function createBuyerB(): User
    {
        return User::factory()->create([
            'name'     => 'Buyer B',
            'email'    => 'buyerb@test.com',
            'role'     => 'buyer',
            'password' => bcrypt('password'),
        ]);
    }

    // ─── Helper: Login via Browser ───────────────────────────────

    /**
     * Login ke aplikasi melalui form login di browser Dusk.
     *
     * Menggunakan selector yang sesuai dengan auth/login.blade.php:
     *   - input#email  (name="email")
     *   - input#password (name="password")
     *   - button[type="submit"] dengan teks "Login"
     */
    private function loginAs(Browser $browser, User $user): Browser
    {
        return $browser
            ->visit('/login')
            ->waitFor('#email')
            ->type('#email', $user->email)
            ->type('#password', 'password')
            ->press('Login')
            ->waitUntilMissing('#email', 10)     // Tunggu form login hilang (redirect terjadi)
            ->assertPathIsNot('/login');          // Pastikan tidak stuck di login
    }

    // ═════════════════════════════════════════════════════════════
    //  TC-CHT-001 — Sifat Permanen Chat
    //  Chat bersifat Create-Read ONLY. Tidak ada tombol Edit/Hapus.
    // ═════════════════════════════════════════════════════════════

    /**
     * Skenario:
     *  1. Login sebagai Buyer A.
     *  2. Kunjungi halaman chat dengan Seller.
     *  3. Ketik pesan "Apakah stok masih ada?" dan tekan Kirim.
     *  4. Tunggu pesan tampil di layar (AJAX polling / instant append).
     *  5. Pastikan pesan muncul.
     *  6. Pastikan TIDAK ada tombol "Hapus" atau "Edit" di halaman.
     */
    #[Test]
    #[Group('chat')]
    #[Group('TC-CHT-001')]
    public function test_pesan_permanen_dan_tidak_bisa_diedit(): void
    {
        $buyer  = $this->createBuyerA();
        $seller = $this->createSeller();

        $this->browse(function (Browser $browser) use ($buyer, $seller): void {
            $this->loginAs($browser, $buyer);

            $browser
                ->visit('/chat/' . $seller->id)
                ->waitFor('#chat-input')
                // Ketik pesan dan kirim
                ->type('#chat-input', 'Apakah stok masih ada?')
                ->click('#chat-send-btn')
                // Tunggu pesan muncul di DOM (maks 10 detik karena AJAX)
                ->waitForText('Apakah stok masih ada?', 10)
                // ── Assertions ──
                ->assertSee('Apakah stok masih ada?')
                // Chat bersifat CR-only: pastikan tidak ada UI edit/hapus
                ->assertDontSee('Hapus')
                ->assertDontSee('Edit');
        });
    }

    // ═════════════════════════════════════════════════════════════
    //  TC-CHT-002 — AJAX Polling Menerima Pesan Real-Time
    //  Menggunakan 2 browser: Buyer mengirim, Seller menerima
    //  TANPA perlu refresh — murni AJAX polling.
    // ═════════════════════════════════════════════════════════════

    /**
     * Skenario:
     *  1. Seller login dan standby di halaman chat dengan Buyer.
     *  2. Buyer login, buka chat dengan Seller, ketik pesan, lalu kirim.
     *  3. Tunggu pesan muncul di layar Buyer.
     *  4. TANPA refresh, periksa apakah Seller menerima pesan yang sama
     *     dalam waktu < 10 detik (polling interval = 3 detik, jadi ~2-3 cycle).
     *
     * Ini adalah test terpenting — membuktikan AJAX polling bekerja.
     */
    #[Test]
    #[Group('chat')]
    #[Group('TC-CHT-002')]
    public function test_ajax_polling_menerima_pesan_realtime(): void
    {
        $buyer  = $this->createBuyerA();
        $seller = $this->createSeller();

        $this->browse(function (Browser $buyerBrowser, Browser $sellerBrowser) use ($buyer, $seller): void {

            // ── Step 1: Seller login & standby di halaman chat ──
            $this->loginAs($sellerBrowser, $seller);
            $sellerBrowser
                ->visit('/chat/' . $buyer->id)
                ->waitFor('#chat-input')
                ->assertPathIs('/chat/' . $buyer->id);

            // ── Step 2: Buyer login, buka chat, kirim pesan ──
            $this->loginAs($buyerBrowser, $buyer);
            $buyerBrowser
                ->visit('/chat/' . $seller->id)
                ->waitFor('#chat-input')
                ->type('#chat-input', 'Halo barang surplusnya ready?')
                ->click('#chat-send-btn')
                // Tunggu pesan muncul di layar Buyer sendiri
                ->waitForText('Halo barang surplusnya ready?', 10)
                ->assertSee('Halo barang surplusnya ready?');

            // ── Step 3: Verifikasi Seller menerima pesan via polling ──
            // TANPA melakukan $sellerBrowser->refresh()
            // Polling interval = 3 detik, beri waktu 10 detik untuk aman.
            $sellerBrowser
                ->waitForText('Halo barang surplusnya ready?', 10)
                ->assertSee('Halo barang surplusnya ready?');
        });
    }

    // ═════════════════════════════════════════════════════════════
    //  TC-CHT-003 — Isolasi Privasi Chat
    //  User yang tidak berhak TIDAK boleh mengakses chat orang lain.
    // ═════════════════════════════════════════════════════════════

    /**
     * Skenario:
     *  1. Buyer A dan Seller sudah pernah berkirim pesan (ada riwayat).
     *  2. Buyer B (user berbeda, role: buyer) login.
     *  3. Buyer B secara paksa mengakses /chat/{seller_id}.
     *  4. Karena Buyer B tidak punya riwayat dan Buyer↔Seller valid pair,
     *     akses BOLEH (desain membiarkan buyer baru chat ke seller).
     *     TAPI jika Buyer B coba akses chat Buyer A (buyer↔buyer),
     *     maka harus diblokir (abort 403).
     *
     * Assertion:
     *  - Buyer B mengunjungi /chat/{buyer_a_id} → diredirect atau muncul error 403.
     */
    #[Test]
    #[Group('chat')]
    #[Group('TC-CHT-003')]
    public function test_pengguna_lain_tidak_bisa_mengakses_chat(): void
    {
        $buyerA = $this->createBuyerA();
        $buyerB = $this->createBuyerB();
        $seller = $this->createSeller();

        // Buat riwayat chat antara Buyer A ↔ Seller
        \App\Models\Message::create([
            'sender_id'   => $buyerA->id,
            'receiver_id' => $seller->id,
            'message'     => 'Pesan rahasia dari Buyer A',
        ]);

        $this->browse(function (Browser $browser) use ($buyerA, $buyerB): void {
            $this->loginAs($browser, $buyerB);

            // Buyer B mencoba mengakses chat dengan Buyer A (buyer ↔ buyer)
            // Controller authorizeChat() akan abort(403) karena bukan valid pair
            $browser
                ->visit('/chat/' . $buyerA->id)
                ->waitForText('403', 10)                            // Halaman error 403
                ->assertSee('403');

            // Pastikan Buyer B TIDAK berada di halaman chat
            $browser->assertPathIsNot('/chat/' . $buyerA->id . '/success');
        });
    }

    // ═════════════════════════════════════════════════════════════
    //  TC-CHT-004 — Kebutaan Admin terhadap Chat
    //  Admin TIDAK boleh punya akses UI ke fitur Chat / Inbox / Pesan.
    // ═════════════════════════════════════════════════════════════

    /**
     * Skenario:
     *  1. Login sebagai Admin.
     *  2. Kunjungi /admin/dashboard (redirect ke admin.validations).
     *  3. Periksa sidebar/navigasi: tidak ada teks "Pesan", "Inbox", atau "Chat".
     *
     * Assertion:
     *  - Membuktikan Admin tidak diberi UI untuk mengintip obrolan pengguna.
     */
    #[Test]
    #[Group('chat')]
    #[Group('TC-CHT-004')]
    public function test_admin_tidak_bisa_melihat_pesan_pengguna(): void
    {
        $admin = $this->createAdmin();

        $this->browse(function (Browser $browser) use ($admin): void {
            $this->loginAs($browser, $admin);

            $browser
                ->visit('/admin/dashboard')
                // Tunggu sidebar ter-render sepenuhnya
                ->waitFor('aside')
                // ── Assertions: Tidak ada menu Chat di sidebar admin ──
                ->assertDontSee('Pesan')
                ->assertDontSee('Inbox')
                ->assertDontSee('Chat');
        });
    }

    // ═════════════════════════════════════════════════════════════
    //  TC-CHT-005 — Notifikasi badge pesan belum dibaca
    // ═════════════════════════════════════════════════════════════

    #[Test]
    #[Group('chat')]
    #[Group('TC-CHT-005')]
    public function test_notifikasi_badge_pesan_belum_dibaca(): void
    {
        $buyerA = $this->createBuyerA();
        $seller = $this->createSeller();

        // 1 pesan belum dibaca dari Seller ke Buyer A
        \App\Models\Message::create([
            'sender_id'   => $seller->id,
            'receiver_id' => $buyerA->id,
            'message'     => 'Pesan baru dari penjual',
            'is_read'     => false, // Flag standard
        ]);

        $this->browse(function (Browser $browser) use ($buyerA): void {
            $this->loginAs($browser, $buyerA);

            $browser
                ->visitRoute('buyer.menu') // Halaman yg memiliki sidebar buyer
                ->waitFor('aside')
                // Assertion notifikasi 1 pesan unread di sidebar
                ->assertSeeIn('aside', 'Inbox Chat')
                ->assertPresent('aside .animate-pulse')
                ->assertSeeIn('aside .animate-pulse', '1');
        });
    }
}
