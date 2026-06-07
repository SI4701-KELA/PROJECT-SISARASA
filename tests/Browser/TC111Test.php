<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC111Test extends DuskTestCase
{

    public function test_toko_pertama_memiliki_highlight(): void
    {
        $this->browse(function (Browser $browser) {
            
            // 1. Selesaikan langkah pencarian Toko Terdekat (termasuk login)
            $browser->visit('/login') 
                ->waitFor('input[type="email"]', 5) 
                ->type('input[type="email"]', 'qwer@gmail.com') 
                ->type('input[type="password"]', 'qwerqwer') 
                ->press('Login') 
                ->pause(2000)
                ->assertPathIs('/buyer/menu')
                
                // 2. Navigasi ke halaman radar
                ->clickLink('Toko Terdekat') 
                
                // 3. Beri waktu yang cukup (4 detik) agar Alpine.js selesai:
                // - Meminta lokasi
                // - Menghitung jarak
                // - Menampilkan daftar toko
                ->pause(4000)
                ->assertPathIs('/buyer/nearby')
                
                // 4. Validasi Utama: Memastikan UI memunculkan badge/label highlight
                // Robot akan memindai seluruh layar untuk mencari teks ini.
                // Pastikan huruf kapitalnya SAMA PERSIS dengan yang ada di UI-mu.
                ->assertSee('3,5 KM');
        });
    }
}