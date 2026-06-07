<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC124Test extends DuskTestCase
{

    public function test_filter_spesifik_kategori_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            
            // 1. Login
            $browser->visit('/login') 
                ->waitFor('input[type="email"]', 5) 
                ->type('input[type="email"]', 'qwer@gmail.com') 
                ->type('input[type="password"]', 'qwerqwer') 
                ->press('Login') 
                ->pause(2000);
        
            // 4. Kategori: Minuman
            $browser->clickLink('Cemilan & Pastry') 
                ->pause(2000)
                ->assertSee('Tidak ada produk');

        });
    }
}