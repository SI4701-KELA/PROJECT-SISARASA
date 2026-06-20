<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;

class TC131Test extends DuskTestCase
{
    use DatabaseTruncation;
    protected $seed = true;

    public function test_login_seller_Katalog_produk(): void
    {
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'uiop@gmail.com'],
            [
                'name' => 'Seller Uiop',
                'role' => 'seller',
                'password' => bcrypt('uiopuiop'),
                'email_verified_at' => now(),
            ]
        );
        \App\Models\Seller::firstOrCreate(
            ['user_id' => $user->id],
            [
                'store_name' => 'Toko Uiop',
                'address' => 'Jl. Test No. 123',
                'latitude' => -6.9147,
                'longitude' => 107.6098,
                'verification_status' => 'approved',
            ]
        );

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