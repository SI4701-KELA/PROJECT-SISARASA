<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;

class TC124Test extends DuskTestCase
{
    use DatabaseTruncation;
    protected $seed = true;

    public function test_filter_spesifik_kategori_kosong(): void
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

        $categories = ['Makanan Berat', 'Cemilan & Pastry', 'Minuman', 'Sayuran & Buah'];
        foreach ($categories as $category) {
            \App\Models\Category::firstOrCreate(['name' => $category]);
        }

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