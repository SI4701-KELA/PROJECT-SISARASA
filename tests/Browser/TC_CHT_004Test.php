<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * TC-CHT-004 — Admin Tidak Memiliki Akses UI ke Fitur Chat
 *
 * Memverifikasi bahwa Admin tidak diberikan menu "Pesan", "Inbox", atau "Chat"
 * di sidebar navigasi admin. Admin tidak dapat mengintip obrolan pengguna
 * karena menu tersebut memang tidak dirender di layout admin.
 *
 * Desain Privasi:
 *   - Chat hanya ada di antara Buyer ↔ Seller.
 *   - Admin sengaja dieksklusi dari UI Chat untuk menjaga privasi transaksi.
 *
 * Precondition:
 *   - User Admin terdaftar di database.
 *   - Dashboard admin (/admin/dashboard) dapat diakses.
 *
 * Login:
 *   - Aktor  : Admin
 *   - URL    : /login → /admin/dashboard
 *   - Email  : admin@chat.test
 *   - Password: password
 *
 * Steps:
 *   1. Login sebagai Admin melalui form /login.
 *   2. Kunjungi /admin/dashboard (redirect ke halaman admin utama).
 *   3. Tunggu elemen sidebar <aside> ter-render sepenuhnya.
 *   4. Periksa sidebar — pastikan tidak ada teks "Pesan", "Inbox", "Chat".
 *
 * Input:
 *   - URL yang dikunjungi: /admin/dashboard
 *
 * Expected Result:
 *   - assertDontSee('Pesan') — tidak ada menu "Pesan" di sidebar.
 *   - assertDontSee('Inbox') — tidak ada menu "Inbox" di sidebar.
 *   - assertDontSee('Chat')  — tidak ada menu "Chat" di sidebar.
 */
#[Group('chat')]
#[Group('TC-CHT-004')]
class TC_CHT_004Test extends ChatTestCase
{
    #[Test]
    public function test_admin_tidak_memiliki_menu_chat_di_sidebar(): void
    {
        // ── Seed: Admin ──
        $admin = $this->createAdmin();

        $this->browse(function (Browser $browser) use ($admin): void {
            // ── Step 1: Login sebagai Admin ──
            // Email: admin@chat.test | Password: password
            $this->loginAs($browser, $admin);

            $browser
                // ── Step 2: Kunjungi dashboard admin ──
                ->visit('/admin/dashboard')

                // ── Step 3: Tunggu sidebar ter-render sepenuhnya ──
                ->waitFor('aside')

                // ── Assert 1: Tidak ada menu "Pesan" di sidebar ──
                ->assertDontSee('Pesan')

                // ── Assert 2: Tidak ada menu "Inbox" di sidebar ──
                ->assertDontSee('Inbox')

                // ── Assert 3: Tidak ada menu "Chat" di sidebar ──
                ->assertDontSee('Chat');
        });
    }
}
