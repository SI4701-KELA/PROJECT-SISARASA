<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;

class TC113Test extends DuskTestCase
{
    use DatabaseTruncation;
    protected $seed = true;
    /**
     * TC-11.3: Menguji fungsionalitas Highlight pada toko terdekat.
     * 
     * Skenario:
     * 1. Selesaikan langkah pencarian Toko Terdekat.
     * 2. Periksa desain kartu toko pada urutan pertama (paling atas).
     * 
     * Expected: Toko di urutan pertama memiliki elemen UI yang berbeda/menonjol
     * (badge "Super Dekat", border oranye) dibandingkan toko di bawahnya.
     * 
     * CATATAN TEKNIS:
     * Chrome headless tidak mendukung pop-up izin Geolocation, jadi kita
     * langsung kunjungi /buyer/nearby?lat=...&lng=... dengan koordinat palsu
     * yang sangat dekat dengan Warung Nasi Budi (lat=-6.9147, lng=107.6098).
     */
    public function test_highlight_toko_terdekat(): void
    {
        // Forcefully create 2 sellers: one close (< 5 KM) and one far (> 5 KM but < 50 KM)
        $sellersData = [
            ['email' => 'toko1@mock.com', 'name' => 'Toko 1 Dekat', 'lat' => -6.9147, 'lng' => 107.6098], // 0.0 KM
            ['email' => 'toko2@mock.com', 'name' => 'Toko 2 Jauh', 'lat' => -6.9500, 'lng' => 107.5500],  // ~7.7 KM
        ];

        foreach ($sellersData as $data) {
            $sellerUser = User::firstOrCreate(
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

        $buyer = User::where('role', 'buyer')->first();
        if (!$buyer) {
            $buyer = User::create([
                'name' => 'Test Buyer',
                'email' => 'testbuyer@gmail.com',
                'password' => bcrypt('password123'),
                'role' => 'buyer',
                'email_verified_at' => now(),
            ]);
        }

        $this->browse(function (Browser $browser) use ($buyer) {
            // 1. Login otomatis sebagai pembeli
            $browser->loginAs($buyer)

                // 2. Kunjungi halaman Toko Terdekat dengan koordinat palsu
                //    Koordinat ini sangat dekat dengan Warung Nasi Budi (-6.9147, 107.6098)
                //    sehingga toko tersebut muncul pertama dengan jarak ≈ 0 KM (< 5 KM)
                ->visit('/buyer/nearby?lat=-6.9150&lng=107.6100')
                ->pause(2000)
                
                // 3. Pastikan lokasi berhasil ditemukan
                ->assertSee('Titik Lokasi Anda Ditemukan');

            // =============================================================
            // 4. VALIDASI: Badge "Super Dekat" ada di halaman
            // =============================================================
            // Blade menggunakan CSS `uppercase`, sehingga teks tampil "SUPER DEKAT".
            // Kita cek keberadaan badge via HTML source karena assertSee mencocokkan
            // teks visual (yang sudah di-uppercase oleh CSS text-transform).
            $hasBadge = $browser->script(
                "return document.body.innerHTML.includes('Super Dekat')"
            );
            $this->assertTrue(
                $hasBadge[0],
                'Halaman harus menampilkan badge "Super Dekat" untuk toko terdekat.'
            );

            // =============================================================
            // 5. VALIDASI: Kartu pertama punya class HIGHLIGHT (border oranye)
            // =============================================================
            $firstCardClasses = $browser->script(
                "return document.querySelector('.grid > div:first-child').className"
            );
            
            // Kartu pertama harus memiliki border-orange-400 (highlight toko terdekat)
            $this->assertStringContainsString(
                'border-orange-400',
                $firstCardClasses[0],
                'Kartu toko pertama (terdekat) harus memiliki border oranye sebagai highlight.'
            );

            // Kartu pertama harus memiliki ring-4 (efek glow oranye)
            $this->assertStringContainsString(
                'ring-4',
                $firstCardClasses[0],
                'Kartu toko pertama harus memiliki ring oranye sebagai pembeda visual.'
            );

            // =============================================================
            // 6. VALIDASI: Header kartu pertama pakai gradient (menonjol)
            // =============================================================
            $firstHeaderClasses = $browser->script(
                "return document.querySelector('.grid > div:first-child > div:first-child').className"
            );
            $this->assertStringContainsString(
                'bg-gradient-to-r',
                $firstHeaderClasses[0],
                'Header kartu pertama harus menggunakan gradient sebagai highlight.'
            );

            // =============================================================
            // 7. VALIDASI PEMBANDING: Pastikan ada kartu TANPA highlight
            //    (artinya highlight hanya untuk toko terdekat, bukan semua)
            // =============================================================
            $totalCards = $browser->script(
                "return document.querySelectorAll('.grid > div').length"
            );

            if ($totalCards[0] > 1) {
                // Cari apakah ada minimal 1 kartu yang TIDAK punya border-orange-400
                $nonHighlightCount = $browser->script(
                    "return Array.from(document.querySelectorAll('.grid > div')).filter(el => !el.className.includes('border-orange-400')).length"
                );

                $this->assertGreaterThan(
                    0,
                    $nonHighlightCount[0],
                    'Harus ada minimal satu kartu toko yang TIDAK di-highlight (tanpa border-orange-400).'
                );
            }

            // Pastikan jarak dalam format KM ditampilkan
            $browser->assertSee('KM');
        });
    }
}
