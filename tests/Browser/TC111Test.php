<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;

class TC111Test extends DuskTestCase
{
    use DatabaseTruncation;
    protected $seed = true;

    public function test_toko_pertama_memiliki_highlight(): void
    {
        // Forcefully create a seller exactly at the mocked coordinates
        // to ensure it is always 0.0 KM and gets the "Super Dekat" badge
        $sellerUser = \App\Models\User::firstOrCreate(
            ['email' => 'budi_mock@seller.com'],
            ['name' => 'Budi Mock', 'password' => bcrypt('password123'), 'role' => 'seller', 'email_verified_at' => now(), 'is_banned' => false]
        );
        \App\Models\Seller::firstOrCreate(
            ['user_id' => $sellerUser->id],
            [
                'store_name' => 'Warung Nasi Budi Mock',
                'address' => 'Jl. Braga Mock',
                'latitude' => -6.9147,
                'longitude' => 107.6098,
                'verification_status' => 'approved',
            ]
        );

        $buyer = \App\Models\User::firstOrCreate(
            ['email' => 'qwer@gmail.com'],
            [
                'name' => 'Buyer QWER',
                'role' => 'buyer',
                'password' => bcrypt('qwerqwer'),
                'email_verified_at' => now(),
            ]
        );

        $this->browse(function (Browser $browser) {
            
            // 1. Selesaikan langkah pencarian Toko Terdekat (termasuk login)
            $browser->visit('/login') 
                ->waitFor('input[type="email"]', 5) 
                ->type('input[type="email"]', 'qwer@gmail.com') 
                ->type('input[type="password"]', 'qwerqwer') 
                ->press('Login') 
                ->pause(2000)
                ->assertPathIs('/buyer/menu')
                
                // 2. Navigasi ke halaman radar
                ->clickLink('Toko Terdekat') 
                ->pause(1000)
                
                // Force specific coordinates (Bandung, exact same as Budi's store) to make test deterministic
                ->visit('/buyer/nearby?lat=-6.9147&lng=107.6098')
                ->pause(2000)
                ->assertPathIs('/buyer/nearby')
                ->storeSource('nearby_tc111_failed')
                // 4. Validasi Utama: Memastikan UI memunculkan badge/label highlight
                // Robot akan memindai seluruh layar untuk mencari teks ini.
                ->assertSee('SUPER DEKAT')
                ->assertSee('0,0 KM');
        });
    }
}