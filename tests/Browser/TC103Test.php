<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC103Test extends DuskTestCase
{

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
                ->assertPathIs('/buyer/nearby')



                ->clickLink('Daftar Menu') 
                ->pause(3000)
                ->assertPathIs('/buyer/menu')
                ->clickLink('Toko Terdekat') 
                ->pause(3000)
                ->assertPathIs('/buyer/nearby');


        });
    }
}