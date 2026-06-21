<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;

class TC112Test extends DuskTestCase
{
    use DatabaseTruncation;
    protected $seed = true;
    /**
     * TC-11.1: Menguji akurasi pengurutan data jarak (Sorting).
     * Skenario: Memastikan toko terdekat (<= 5 KM) muncul dan memiliki label "Super Dekat".
     */
    public function test_akurasi_pengurutan_jarak(): void
    {
        // Forcefully create 4 sellers with varying distances within 50 KM
        $sellersData = [
            ['email' => 'toko1@mock.com', 'name' => 'Toko 1', 'lat' => -6.9147, 'lng' => 107.6098], // 0.0 KM
            ['email' => 'toko2@mock.com', 'name' => 'Toko 2', 'lat' => -6.9170, 'lng' => 107.6090], // ~0.3 KM
            ['email' => 'toko3@mock.com', 'name' => 'Toko 3', 'lat' => -6.9000, 'lng' => 107.6000], // ~2.0 KM
            ['email' => 'toko4@mock.com', 'name' => 'Toko 4', 'lat' => -6.8500, 'lng' => 107.5000], // ~14.1 KM (seharusnya difilter karena > 10 KM)
        ];

        foreach ($sellersData as $data) {
            $sellerUser = \App\Models\User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => bcrypt('password123'), 'role' => 'seller', 'email_verified_at' => now(), 'is_banned' => false]
            );
            \App\Models\Seller::firstOrCreate(
                ['user_id' => $sellerUser->id],
                [
                    'store_name' => $data['name'],
                    'address' => 'Mock Address',
                    'latitude' => $data['lat'],
                    'longitude' => $data['lng'],
                    'verification_status' => 'approved',
                ]
            );
        }

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
            
            // 1 & 2. Login sebagai pembeli
            $browser->visit('/login') 
                ->waitFor('input[type="email"]', 5) 
                ->type('input[type="email"]', 'qwer@gmail.com') 
                ->type('input[type="password"]', 'qwerqwer') 
                ->press('Login') 
                ->pause(2000)
                ->assertPathIs('/buyer/menu')
                
                // 3. Klik menu "Toko Terdekat"
                ->clickLink('Toko Terdekat') 
                ->pause(1000)
                
                // Force specific coordinates (Bandung)
                ->visit('/buyer/nearby?lat=-6.9147&lng=107.6098')
                ->pause(2000)
                ->assertPathIs('/buyer/nearby')
                
                // 4. Perhatikan urutan daftar toko yang muncul.
                // TANDA KEBERHASILAN: Memastikan UI memunculkan badge "Super Dekat"
                ->assertSee('SUPER DEKAT')
                ->assertSee('0,0 KM')
                ->assertSee('0,3 KM')
                ->assertSee('2,0 KM')
                ->assertDontSee('14,1 KM')
                ->assertDontSee('Toko 4');
        });
    }
}