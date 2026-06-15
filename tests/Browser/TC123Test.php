<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC123Test extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Setup ekosistem data uji:
     * - 1 Buyer
     * - Beberapa Kategori (tanpa produk sama sekali)
     */
    private function setupEcosystem()
    {
        // 1. Buat Buyer
        $buyer = User::firstOrCreate(
            ['email' => 'buyer_tc123@test.com'],
            [
                'name' => 'Buyer TC123',
                'role' => 'buyer',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Buat Kategori pendukung
        Category::firstOrCreate(['name' => 'Makanan Berat']);
        Category::firstOrCreate(['name' => 'Cemilan & Pastry']);
        Category::firstOrCreate(['name' => 'Minuman']);

        // JANGAN membuat produk aktif sama sekali

        return compact('buyer');
    }

    /**
     * TC-12.3: Menguji tampilan halaman ketika tidak ada produk yang dibuat oleh seller (Empty State).
     */
    public function test_tampilan_empty_state_daftar_menu_kosong(): void
    {
        $eco = $this->setupEcosystem();

        $this->browse(function (Browser $browser) use ($eco) {
            // Langkah 1: Login dan buka halaman Daftar Menu
            $browser->loginAs($eco['buyer'])
                ->visit('/buyer/menu')
                // Langkah 3: Tunggu proses pemuatan selesai
                ->waitForText('Katalog Menu Sisa Rasa')
                ->pause(5000)
                ->assertPathIs('/buyer/menu');

            // Expected Result:
            // 1. Halaman berhasil terbuka tanpa error/crash
            // 2. Teks Empty State "Tidak ada produk" muncul
            $browser->assertSee('Tidak ada produk');

            // 3. Card produk `.product-card` tidak muncul
            $browser->assertMissing('.product-card');

            // 4. Tidak ada nama produk yang tampil
            // Kita pastikan selector tombol add-to-cart atau info porsi tidak dirender
            $browser->assertDontSee('Porsi');
        });
    }
}