<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC133Test extends DuskTestCase
{

    public function test_login_seller_Katalog_produk_diskon(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login') 
                ->waitFor('input[type="email"]', 5) 
                ->type('input[type="email"]', 'uiop@gmail.com') 
                ->type('input[type="password"]', 'uiopuiop') 
                ->press('Login') 
                ->pause(2000)
                ->assertPathIs('/seller/profile')
                
                ->clickLink('Katalog Produk') 
                ->pause(3000)
                ->assertPathIs('/seller/products')
                ->assertSee('Aktifkan Diskon');


        });
    }
}