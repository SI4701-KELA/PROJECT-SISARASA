<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;

class TC101Test extends DuskTestCase
{
    use DatabaseTruncation;
    protected $seed = true;
    /**
     * TC-10.1: Menguji alur login dengan email dan password.
     */
    public function test_login_scenario(): void
    {
        $buyer = \App\Models\User::firstOrCreate(
            ['email' => 'qwer@gmail.com'],
            [
                'name' => 'Buyer QWER',
                'role' => 'buyer',
                'password' => bcrypt('qwerqwer'),
                'email_verified_at' => now(),
            ]
        );

        $this->browse(function (Browser $browser) {
            $browser->visit('/login') 
                ->waitFor('input[type="email"]', 5) 
                ->type('input[type="email"]', 'qwer@gmail.com') 
                ->type('input[type="password"]', 'qwerqwer') 
                ->press('Login') 
                ->pause(2000)
                ->assertPathIs('/buyer/menu')
                
                // PERBAIKAN 1: Gunakan clickLink untuk navigasi menu (tag <a>)
                ->clickLink('Toko Terdekat') 
                
                // Tunggu proses loading halaman dan proses Alpine.js mencari lokasi
                ->pause(3000)
                
                // PERBAIKAN 2: Baris klik 'Allow this time' DIHAPUS. 
                // Asumsinya DuskTestCase sudah di-setting auto-allow location.
                
                // Validasi apakah berhasil masuk ke halaman nearby
                ->assertPathIs('/buyer/nearby');
        });
    }
}