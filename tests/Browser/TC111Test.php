<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC111Test extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Set up user buyer dan minimal 3 seller dengan jarak berbeda dari koordinat buyer (-6.9147, 107.6098):
     * - Toko A: 0.5 KM (lat: -6.9147, lng: 107.6143)
     * - Toko B: 1.2 KM (lat: -6.9147, lng: 107.6207)
     * - Toko C: 3.0 KM (lat: -6.9147, lng: 107.6370)
     */
    private function setupEcosystem()
    {
        // 1. Buat data user Buyer
        $buyer = User::firstOrCreate(
            ['email' => 'buyer_tc111@test.com'],
            [
                'name' => 'Buyer TC111',
                'role' => 'buyer',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Buat data Toko A (Jarak ~0.5 KM)
        $sellerUserA = User::firstOrCreate(
            ['email' => 'toko_a@test.com'],
            [
                'name' => 'Seller A',
                'role' => 'seller',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        Seller::firstOrCreate(
            ['user_id' => $sellerUserA->id],
            [
                'store_name' => 'Toko A',
                'address' => 'Jl. Toko A',
                'latitude' => -6.9147,
                'longitude' => 107.6143,
                'verification_status' => 'approved',
            ]
        );

        // 3. Buat data Toko B (Jarak ~1.2 KM)
        $sellerUserB = User::firstOrCreate(
            ['email' => 'toko_b@test.com'],
            [
                'name' => 'Seller B',
                'role' => 'seller',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        Seller::firstOrCreate(
            ['user_id' => $sellerUserB->id],
            [
                'store_name' => 'Toko B',
                'address' => 'Jl. Toko B',
                'latitude' => -6.9147,
                'longitude' => 107.6207,
                'verification_status' => 'approved',
            ]
        );

        // 4. Buat data Toko C (Jarak ~3.0 KM)
        $sellerUserC = User::firstOrCreate(
            ['email' => 'toko_c@test.com'],
            [
                'name' => 'Seller C',
                'role' => 'seller',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        Seller::firstOrCreate(
            ['user_id' => $sellerUserC->id],
            [
                'store_name' => 'Toko C',
                'address' => 'Jl. Toko C',
                'latitude' => -6.9147,
                'longitude' => 107.6370,
                'verification_status' => 'approved',
            ]
        );

        return compact('buyer');
    }

    /**
     * TC-11.1: Menguji akurasi pengurutan data jarak (Sorting).
     */
    public function test_akurasi_pengurutan_jarak_ascending(): void
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

            // Langkah 1: Buka halaman Toko Terdekat & Login
            $browser->loginAs($eco['buyer'])
                ->visit('/buyer/nearby')
                // Langkah 2: Tunggu proses deteksi lokasi selesai dan redirect
                ->waitForText('Titik Lokasi Anda Ditemukan')
                ->assertPathIs('/buyer/nearby');

            // Assertion 1: Minimal terdapat 3 toko yang tampil pada UI
            $totalSellers = $browser->script("return document.querySelectorAll('.grid > div').length")[0];
            $this->assertGreaterThanOrEqual(3, $totalSellers, 'Jumlah toko yang tampil di UI kurang dari 3.');

            // Assertion 2 & 3: Jarak setiap toko berhasil dibaca dari UI dan diubah menjadi numeric
            $distances = $browser->script("
                return Array.from(document.querySelectorAll('.grid > div')).map(el => {
                    let badges = Array.from(el.querySelectorAll('div, span, p'));
                    let kmBadge = badges.find(b => b.textContent.includes('KM'));
                    if (kmBadge) {
                        let text = kmBadge.textContent.replace('KM', '').trim();
                        text = text.replace(',', '.');
                        return parseFloat(text);
                    }
                    return null;
                }).filter(v => v !== null);
            ")[0];

            $this->assertCount($totalSellers, $distances, 'Gagal membaca jarak numerik untuk seluruh toko yang tampil.');

            // Assertion 4: Lakukan assertion bahwa urutan data adalah ascending
            $sortedDistances = $distances;
            sort($sortedDistances);

            $this->assertEquals($sortedDistances, $distances, 'Urutan data jarak toko pada UI tidak urut secara ascending.');
        });
    }
}