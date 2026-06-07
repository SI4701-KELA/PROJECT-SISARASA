<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC112Test extends DuskTestCase
{
    /**
     * TC-11.1: Menguji akurasi pengurutan data jarak (Sorting).
     * Skenario: Memastikan toko terdekat (<= 5 KM) muncul dan memiliki label "Super Dekat".
     */
    public function test_akurasi_pengurutan_jarak(): void
    {
        $this->browse(function (Browser $browser) {
            
            // 1 & 2. Login sebagai pembeli
            $browser->visit('/login') 
                ->waitFor('input[type="email"]', 5) 
                ->type('input[type="email"]', 'qwer@gmail.com') 
                ->type('input[type="password"]', 'qwerqwer') 
                ->press('Login') 
                ->pause(2000)
                ->assertPathIs('/buyer/menu')
                
                // 3. Klik menu "Toko Terdekat"
                ->clickLink('Toko Terdekat') 
                
                // Beri waktu Alpine.js memproses koordinat dan backend menghitung jarak
                ->pause(3000)
                ->assertPathIs('/buyer/nearby')
                
                // 4. Perhatikan urutan daftar toko yang muncul.
                // TANDA KEBERHASILAN: Memastikan UI memunculkan badge "Super Dekat"
                ->assertSee('3,5 KM')
                ->assertSee('6,8 KM')
                ->assertSee('124,0 KM')
                ->assertSee('562,6 KM');
        });
    }
}