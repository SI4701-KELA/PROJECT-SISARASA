<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC125Test extends DuskTestCase
{

    public function test_filter_kategori_perubahan_tombol_warna(): void
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
            
  
            $browser->visit('/login') 
                ->waitFor('input[type="email"]', 5) 
                ->type('input[type="email"]', 'qwer@gmail.com') 
                ->type('input[type="password"]', 'qwerqwer') 
                ->press('Login') 
                ->pause(2000);
        
            $browser->clickLink('Cemilan & Pastry') 
                ->pause(2000);
                $classes = $browser->script("return Array.from(document.querySelectorAll('a')).find(a => a.textContent.includes('Cemilan & Pastry')).className;")[0];
                $this->assertStringContainsString('bg-[#c04b36]', $classes, 'Kategori aktif harus memiliki background warna aktif');

        });
    }
}