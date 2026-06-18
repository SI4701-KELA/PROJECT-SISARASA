<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;

class TC122Test extends DuskTestCase
{
    use DatabaseTruncation;
    protected $seed = true;
    /**
     * TC-12.2: Menguji penyaringan berdasarkan kategori spesifik.
     */
    public function test_filter_semua_kategori(): void
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
            
            // 2. Kategori: Makanan Berat
            $browser->clickLink('Makanan Berat') 
                ->pause(2000)
                ->assertPathIs('/buyer/menu')            // Cek path utamanya
                ->assertQueryStringHas('category_id', '1'); // Cek parameternya

            // 3. Kategori: Cemilan & Pastry
            $browser->clickLink('Cemilan & Pastry') 
                ->pause(2000)
                ->assertPathIs('/buyer/menu')
                ->assertQueryStringHas('category_id', '2');

            // 4. Kategori: Minuman
            $browser->clickLink('Minuman') 
                ->pause(2000)
                ->assertPathIs('/buyer/menu')
                ->assertQueryStringHas('category_id', '3');

            // 5. Kategori: Sayuran & Buah
            $browser->clickLink('Sayuran & Buah') 
                ->pause(2000)
                ->assertPathIs('/buyer/menu')
                ->assertQueryStringHas('category_id', '4'); // Pastikan ID ini sesuai dengan database-mu!
        });
    }
}