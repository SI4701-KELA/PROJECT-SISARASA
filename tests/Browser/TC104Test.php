<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC104Test extends DuskTestCase
{
    /**
     * TC-10.4: Menguji perilaku aplikasi tanpa interaksi pengguna.
     * Skenario: Navigasi ke menu lain selain "Toko Terdekat" tidak boleh memicu fitur lokasi.
     */
    public function test_fitur_lokasi_tidak_aktif_di_halaman_lain(): void
    {
        $this->browse(function (Browser $browser) {
            
            // 1. Jalur VIP: Login otomatis sebagai pembeli (misal ID 1)
            $buyer = User::where('role', 'buyer')->first() ?? User::factory()->create(['role' => 'buyer']);
            
            $browser->loginAs($buyer)
                
                // 2. Buka halaman Dashboard / Daftar Menu (halaman awal setelah login)
                ->visit('/buyer/menu')
                
                // 3. Diam di halaman tersebut sejenak
                ->pause(2000)
                
                // 4. VALIDASI 1: Pastikan komponen UI Radar Lokasi tidak muncul sama sekali
                ->assertDontSee('Mencari Lokasi Anda...')
                ->assertDontSee('Titik Lokasi Anda Ditemukan')
                
                // 5. VALIDASI 2: Pastikan URL bersih dari parameter koordinat
                ->assertQueryStringMissing('lat')
                ->assertQueryStringMissing('lng')
                
                // 6. Pindah dan klik menu lain di sidebar (misalnya 'Riwayat Pesanan')
                ->clickLink('Riwayat Pesanan')
                
                // 7. Beri waktu halaman selesai dimuat
                ->pause(2000)
                
                // 8. Lakukan validasi yang sama di halaman baru ini untuk memastikan keamanannya
                ->assertDontSee('Mencari Lokasi Anda...')
                ->assertQueryStringMissing('lat')
                ->assertQueryStringMissing('lng');
        });
    }
}