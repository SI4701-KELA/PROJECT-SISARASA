<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC131Test extends DuskTestCase
{

    public function test_login_seller_Katalog_produk(): void
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
                ->assertSee('Kelola makanan yang tersedia dan surplus untuk didiskon sesuai jam.');

        });
    }
}