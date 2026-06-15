<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC102Test extends DuskTestCase
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

    public function test_buyer_denies_location_permission_shows_error_message(): void
    {
        $eco = $this->setupEcosystem();

        $this->browse(function (Browser $browser) use ($eco) {
            // Gunakan Chromium send_command untuk memaksa Geolocation mengembalikan error PERMISSION_DENIED (code 1) di setiap page load
            $browser->driver->executeCustomCommand('/session/:sessionId/chromium/send_command_and_get_result', 'POST', [
                'cmd' => 'Page.addScriptToEvaluateOnNewDocument',
                'params' => [
                    'source' => '
                        Object.defineProperty(navigator, "geolocation", {
                            value: {
                                getCurrentPosition: function(success, error, options) {
                                    error({
                                        code: 1,
                                        message: "User denied Geolocation"
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
                
                // Klik menu "Toko Terdekat"
                ->clickLink('Toko Terdekat') 
                
                // Tunggu hingga pesan "Akses Lokasi Ditolak" muncul
                ->waitForText('Akses Lokasi Ditolak')
                ->pause(5000)
                ->assertPathIs('/buyer/nearby')
                ->pause(5000)
                // Validasi daftar toko TIDAK muncul
                ->assertDontSee('Toko PbiTigaDua')
                // Validasi user tetap dapat bernavigasi menggunakan aplikasi (misal kembali ke menu)
                ->clickLink('Daftar Menu')
                ->assertPathIs('/buyer/menu');
        });
    }
}
