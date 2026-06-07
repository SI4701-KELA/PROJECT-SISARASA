<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC125Test extends DuskTestCase
{

    public function test_filter_kategori_perubahan_tombol_warna(): void
    {
        $this->browse(function (Browser $browser) {
            
  
            $browser->visit('/login') 
                ->waitFor('input[type="email"]', 5) 
                ->type('input[type="email"]', 'qwer@gmail.com') 
                ->type('input[type="password"]', 'qwerqwer') 
                ->press('Login') 
                ->pause(2000);
        
            $browser->clickLink('Cemilan & Pastry') 
                ->pause(2000)
                ->assertHasClass('.Cemilan & Pastry', 'bg-gray-900');

        });
    }
}