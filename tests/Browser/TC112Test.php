<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC112Test extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Set up user buyer dan 3 seller:
     * - Toko Dekat A: ~0.3 KM (lat: -6.9147, lng: 107.6125)
     * - Toko Dekat B: ~1.9 KM (lat: -6.9147, lng: 107.6270)
     * - Toko Sangat Jauh: ~50.0 KM (lat: -6.9147, lng: 108.0628)
     */
    private function setupEcosystem()
    {
        // 1. Buat data user Buyer
        $buyer = User::firstOrCreate(
            ['email' => 'buyer_tc112@test.com'],
            [
                'name' => 'Buyer TC112',
                'role' => 'buyer',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Buat data Toko Dekat A (~0.3 KM)
        $sellerUserA = User::firstOrCreate(
            ['email' => 'toko_dekat_a@test.com'],
            [
                'name' => 'Toko Dekat A',
                'role' => 'seller',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        Seller::firstOrCreate(
            ['user_id' => $sellerUserA->id],
            [
                'store_name' => 'Toko Dekat A',
                'address' => 'Jl. Toko Dekat A',
                'latitude' => -6.9147,
                'longitude' => 107.6125,
                'verification_status' => 'approved',
            ]
        );

        // 3. Buat data Toko Dekat B (~1.9 KM)
        $sellerUserB = User::firstOrCreate(
            ['email' => 'toko_dekat_b@test.com'],
            [
                'name' => 'Toko Dekat B',
                'role' => 'seller',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        Seller::firstOrCreate(
            ['user_id' => $sellerUserB->id],
            [
                'store_name' => 'Toko Dekat B',
                'address' => 'Jl. Toko Dekat B',
                'latitude' => -6.9147,
                'longitude' => 107.6270,
                'verification_status' => 'approved',
            ]
        );

        // 4. Buat data Toko Sangat Jauh (~50.0 KM)
        $sellerUserFar = User::firstOrCreate(
            ['email' => 'toko_sangat_jauh@test.com'],
            [
                'name' => 'Toko Sangat Jauh',
                'role' => 'seller',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        Seller::firstOrCreate(
            ['user_id' => $sellerUserFar->id],
            [
                'store_name' => 'Toko Sangat Jauh',
                'address' => 'Jl. Toko Sangat Jauh',
                'latitude' => -6.9147,
                'longitude' => 108.0628,
                'verification_status' => 'approved',
            ]
        );

        return compact('buyer');
    }

    /**
     * TC-11.2: Menguji bahwa sistem hanya menampilkan toko yang berada di sekitar lokasi Buyer.
     */
    public function test_hanya_tampilkan_toko_dalam_radius(): void
    {
        $eco = $this->setupEcosystem();

        $this->browse(function (Browser $browser) use ($eco) {
            // Gunakan Chromium send_command untuk memaksa geolocation mengembalikan koordinat sukses di setiap page load
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

            // Langkah 1 & 3: Buka halaman Toko Terdekat & Login
            $browser->loginAs($eco['buyer'])
                ->visit('/buyer/nearby')
                // Tunggu deteksi lokasi mock selesai
                ->waitForText('Titik Lokasi Anda Ditemukan')
                ->assertPathIs('/buyer/nearby');

            // Langkah 4: Pastikan Toko Dekat A dan Toko Dekat B tampil di halaman UI
            $browser->assertSee('Toko Dekat A')
                ->assertSee('Toko Dekat B');

            // Langkah 5: Pastikan Toko Sangat Jauh tidak tampil di halaman UI (karena berada di luar radius)
            $browser->assertDontSee('Toko Sangat Jauh');
        });
    }
}