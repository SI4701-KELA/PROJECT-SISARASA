<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC113Test extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Set up user buyer dan data pendukung.
     * Tidak membuat seller aktif yang dapat tampil pada halaman Toko Terdekat.
     * Kita buat seller dengan status pending agar sistem tetap memiliki data seller
     * tetapi tidak ditampilkan pada halaman Toko Terdekat (karena belum approved).
     */
    private function setupEcosystem()
    {
        // 1. Buat data user Buyer
        $buyer = User::firstOrCreate(
            ['email' => 'buyer_tc113@test.com'],
            [
                'name' => 'Buyer TC113',
                'role' => 'buyer',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Buat data Seller dengan status pending (tidak akan tampil)
        $sellerUserPending = User::firstOrCreate(
            ['email' => 'toko_pending@test.com'],
            [
                'name' => 'Seller Pending',
                'role' => 'seller',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        Seller::firstOrCreate(
            ['user_id' => $sellerUserPending->id],
            [
                'store_name' => 'Toko Pending',
                'address' => 'Jl. Toko Pending',
                'latitude' => -6.9147,
                'longitude' => 107.6098,
                'verification_status' => 'pending', // status pending agar tidak lolos filter approved
            ]
        );

        return compact('buyer');
    }

    /**
     * TC-11.3: Menguji tampilan saat tidak ada UMKM yang tersedia (Empty State).
     */
    public function test_tampilan_empty_state_saat_tidak_ada_umkm_aktif(): void
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

            // Langkah 1 & 2: Login dan buka halaman Toko Terdekat
            $browser->loginAs($eco['buyer'])
                ->visit('/buyer/nearby')
                // Langkah 3: Tunggu proses pencarian toko selesai
                ->waitForText('Titik Lokasi Anda Ditemukan')
                ->assertPathIs('/buyer/nearby');

            // Langkah 4 & Expected Result:
            // 1. Sistem tidak mengalami error / crash
            // 2. Teks Empty State tampil
            $browser->assertSee('Tidak Ada Toko Terdekat');

            // 3. Grid container daftar toko tidak muncul
            $browser->assertMissing('div.grid');

            // 4. Nama toko pending tidak muncul
            $browser->assertDontSee('Toko Pending');

            // 5. Tidak ada tombol "Kunjungi Toko" (karena tidak ada toko yang tampil)
            $browser->assertDontSee('Kunjungi Toko');
        });
    }
}
