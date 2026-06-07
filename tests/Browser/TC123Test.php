<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC123Test extends DuskTestCase
{

    public function test_filter_spesifik_kategori(): void
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
            $browser->clickLink('Minuman') 
                ->pause(2000)
                ->assertPathIs('/buyer/menu')
                ->assertQueryStringHas('category_id', '3')

            ->clickLink('Semua Makanan')
            ->pause(2000)
            ->assertSee('Semua Makanan');





        });
    }
}