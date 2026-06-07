<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC121Test extends DuskTestCase
{

    public function test_default_kategori_semua_makanan(): void
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
            
            // 1 & 2. Login sebagai pembeli dan masuk ke Dashboard
            $browser->visit('/login') 
                ->waitFor('input[type="email"]', 5) 
                ->type('input[type="email"]', 'qwer@gmail.com') 
                ->type('input[type="password"]', 'qwerqwer') 
                ->press('Login') 
                ->pause(2000)
                ->assertPathIs('/buyer/menu')
                
                // 3. Klik navigasi "Daftar Menu" 
                // (Meskipun rutenya mungkin sama, kita tetap simulasikan kliknya)
                ->clickLink('Daftar Menu') 
                ->pause(2000)
                
                // 4. Perhatikan tombol kategori yang aktif.
                // Validasi 1: Teks kategorinya muncul di layar
                ->assertSee('Semua Makanan');

        });
    }
}