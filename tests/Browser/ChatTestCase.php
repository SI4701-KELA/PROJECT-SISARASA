<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Base class untuk semua test PBI-30 (Fitur Chat Pembeli & Penjual).
 * Menyimpan semua factory helper dan helper login via form UI.
 *
 * Catatan login: Chat menggunakan loginAs() via form browser (bukan shortcut),
 * karena session AJAX polling pada /chat harus melalui auth form.
 *
 * Kredensial default:
 *   - Buyer A  : email=buyera@chat.test       | password=password
 *   - Buyer B  : email=buyerb@chat.test       | password=password
 *   - Seller   : email=seller@chat.test       | password=password
 *   - Admin    : email=admin@chat.test        | password=password
 */
abstract class ChatTestCase extends DuskTestCase
{
    use DatabaseTruncation;

    // ─── Factory Helpers ─────────────────────────────────────────

    protected function createBuyerA(): User
    {
        return User::factory()->create([
            'name'     => 'Buyer A',
            'email'    => 'buyera@chat.test',
            'role'     => 'buyer',
            'password' => bcrypt('password'),
        ]);
    }

    protected function createBuyerB(): User
    {
        return User::factory()->create([
            'name'     => 'Buyer B',
            'email'    => 'buyerb@chat.test',
            'role'     => 'buyer',
            'password' => bcrypt('password'),
        ]);
    }

    protected function createSeller(): User
    {
        return User::factory()->create([
            'name'     => 'Seller Toko',
            'email'    => 'seller@chat.test',
            'role'     => 'seller',
            'password' => bcrypt('password'),
        ]);
    }

    protected function createAdmin(): User
    {
        return User::factory()->create([
            'name'     => 'Admin Super',
            'email'    => 'admin@chat.test',
            'role'     => 'admin',
            'password' => bcrypt('password'),
        ]);
    }

    // ─── Helper: Login via Form Browser ──────────────────────────

    /**
     * Login ke aplikasi melalui form login di browser Dusk.
     *
     * Menggunakan form UI (bukan ->loginAs() shortcut) agar session AJAX
     * polling pada /chat terbentuk dengan benar melalui proses autentikasi web.
     *
     * Selector:
     *   - input#email     (name="email")
     *   - input#password  (name="password")
     *   - button "Login"
     */
    protected function loginAs(Browser $browser, User $user): Browser
    {
        return $browser
            ->visit('/login')
            ->waitFor('#email')
            ->type('#email', $user->email)
            ->type('#password', 'password')
            ->press('Login')
            ->waitUntilMissing('#email', 10)   // Tunggu form login hilang (redirect terjadi)
            ->assertPathIsNot('/login');        // Pastikan tidak stuck di login
    }
}
