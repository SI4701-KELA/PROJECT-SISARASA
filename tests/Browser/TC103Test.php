<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC103Test extends DuskTestCase
{
    use DatabaseTruncation;

    private function setupEcosystem()
    {
        $buyer = User::firstOrCreate(
            ['email' => 'qwer@gmail.com'],
            [
                'name' => 'Buyer QWER',
                'role' => 'buyer',
                'password' => bcrypt('qwerqwer'),
                'email_verified_at' => now(),
            ]
        );

        $sellerUser = User::firstOrCreate(
            ['email' => 'toko32@test.com'],
            [
                'name' => 'Seller PbiTigaDua',
                'email' => 'toko32@test.com',
                'role' => 'seller',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_banned' => false,
            ]
        );

        $seller = Seller::firstOrCreate(
            ['user_id' => $sellerUser->id],
            [
                'store_name' => 'Toko PbiTigaDua',
                'address' => 'Jl. Pbi Tiga Dua No. 32',
                'latitude' => -6.9147,
                'longitude' => 107.6098,
                'verification_status' => 'approved',
            ]
        );

        return compact('buyer', 'sellerUser', 'seller');
    }

    public function test_persistence_izin_lokasi(): void
    {
        $eco = $this->setupEcosystem();

        $this->browse(function (Browser $browser) use ($eco) {
            // Gunakan Chromium send_command untuk memaksa geolocation mengembalikan koordinat sukses di setiap page load secara persisten
            $browser->driver->executeCustomCommand('/session/:sessionId/chromium/send_command_and_get_result', 'POST', [
                'cmd' => 'Page.addScriptToEvaluateOnNewDocument',
                'params' => [
                    'source' => '
                        Object.defineProperty(navigator, "geolocation", {
                            value: {
                                getCurrentPosition: function(success, error, options) {
                                    success({
                                        coords: {
                                            latitude: -6.9147,
                                            longitude: 107.6098,
                                            accuracy: 100
                                        }
                                    });
                                }
                            },
                            writable: true
                        });
                    '
                ]
            ]);

            $browser->visit('/login') 
                ->waitFor('input[type="email"]', 5) 
                ->type('input[type="email"]', 'qwer@gmail.com') 
                ->type('input[type="password"]', 'qwerqwer') 
                ->press('Login') 
                ->pause(2000)
                ->assertPathIs('/buyer/menu')
                
                // Kunjungan 1: Klik menu "Toko Terdekat"
                ->clickLink('Toko Terdekat') 
                
                // Tunggu deteksi lokasi mock selesai
                ->waitForText('Titik Lokasi Anda Ditemukan')
                ->assertPathIs('/buyer/nearby')
                ->assertSee('Toko PbiTigaDua')
                ->assertSee('Jl. Pbi Tiga Dua No. 32')

                // Navigasi keluar ke Daftar Menu
                ->clickLink('Daftar Menu') 
                ->pause(2000)
                ->assertPathIs('/buyer/menu')

                // Kunjungan 2: Klik menu "Toko Terdekat" kembali
                ->clickLink('Toko Terdekat') 
                // Sistem harus langsung mengingat dan mendeteksi lokasi tanpa re-prompt (langsung tampil daftar toko)
                ->waitForText('Titik Lokasi Anda Ditemukan')
                ->assertPathIs('/buyer/nearby')
                ->assertSee('Toko PbiTigaDua')
                ->assertSee('Jl. Pbi Tiga Dua No. 32');
        });
    }
}