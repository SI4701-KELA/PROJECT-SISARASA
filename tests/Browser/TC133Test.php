<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;

class TC133Test extends DuskTestCase
{
    use DatabaseTruncation;
    protected $seed = true;

    public function test_login_seller_Katalog_produk_diskon(): void
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
        $seller = \App\Models\Seller::firstOrCreate(
            ['user_id' => $user->id],
            [
                'store_name' => 'Toko Uiop',
                'address' => 'Jl. Test No. 123',
                'latitude' => -6.9147,
                'longitude' => 107.6098,
                'verification_status' => 'approved',
            ]
        );

        $category = \App\Models\Category::firstOrCreate(['name' => 'Makanan Berat']);
        
        $product1 = \App\Models\Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Produk Tanpa Diskon',
            'description' => 'Dummy desc',
            'base_price' => 15000,
            'image' => 'dummy.jpg',
        ]);
        \App\Models\Stock::create(['product_id' => $product1->id, 'qty_reg' => 10, 'qty_surplus' => 5]);

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