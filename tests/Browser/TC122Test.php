<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC122Test extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Setup ekosistem data uji:
     * - 1 Buyer
     * - 1 Seller Aktif (approved)
     * - Kategori "Makanan" dan "Minuman"
     * - Produk:
     *   * Minuman: "Es Teh", "Jus Alpukat"
     *   * Makanan: "Nasi Goreng", "Ayam Bakar"
     */
    private function setupEcosystem()
    {
        // 1. Buat Buyer
        $buyer = User::firstOrCreate(
            ['email' => 'buyer_tc122@test.com'],
            [
                'name' => 'Buyer TC122',
                'role' => 'buyer',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Buat Seller Aktif (Approved)
        $sellerUserActive = User::firstOrCreate(
            ['email' => 'seller_tc122@test.com'],
            [
                'name' => 'Seller TC122',
                'role' => 'seller',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        $sellerActive = Seller::firstOrCreate(
            ['user_id' => $sellerUserActive->id],
            [
                'store_name' => 'Toko Menu TC122',
                'address' => 'Jl. Menu TC122',
                'latitude' => -6.9147,
                'longitude' => 107.6098,
                'verification_status' => 'approved',
            ]
        );

        // 3. Buat Kategori
        $catMakanan = Category::firstOrCreate(['name' => 'Makanan']);
        $catMinuman = Category::firstOrCreate(['name' => 'Minuman']);

        // 4. Buat Produk Kategori Minuman
        $esteh = Product::create([
            'seller_id' => $sellerActive->id,
            'category_id' => $catMinuman->id,
            'name' => 'Es Teh',
            'description' => 'Es teh segar',
            'base_price' => 3000,
            'image' => 'default.jpg',
        ]);
        Stock::create(['product_id' => $esteh->id, 'qty_reg' => 10]);

        $jusAlpukat = Product::create([
            'seller_id' => $sellerActive->id,
            'category_id' => $catMinuman->id,
            'name' => 'Jus Alpukat',
            'description' => 'Jus alpukat manis',
            'base_price' => 8000,
            'image' => 'default.jpg',
        ]);
        Stock::create(['product_id' => $jusAlpukat->id, 'qty_reg' => 10]);

        // 5. Buat Produk Kategori Makanan
        $nasigoreng = Product::create([
            'seller_id' => $sellerActive->id,
            'category_id' => $catMakanan->id,
            'name' => 'Nasi Goreng',
            'description' => 'Nasi goreng lezat',
            'base_price' => 12000,
            'image' => 'default.jpg',
        ]);
        Stock::create(['product_id' => $nasigoreng->id, 'qty_reg' => 10]);

        $ayambakar = Product::create([
            'seller_id' => $sellerActive->id,
            'category_id' => $catMakanan->id,
            'name' => 'Ayam Bakar',
            'description' => 'Ayam bakar gurih',
            'base_price' => 15000,
            'image' => 'default.jpg',
        ]);
        Stock::create(['product_id' => $ayambakar->id, 'qty_reg' => 10]);

        return compact('buyer');
    }

    /**
     * TC-12.2: Menguji penyaringan produk berdasarkan kategori tertentu.
     */
    public function test_penyaringan_produk_berdasarkan_kategori(): void
    {
        $eco = $this->setupEcosystem();

        $this->browse(function (Browser $browser) use ($eco) {
            // Login dan kunjungi halaman Daftar Menu
            $browser->loginAs($eco['buyer'])
                ->visit('/buyer/menu')
                ->waitForText('Katalog Menu Sisa Rasa')
                ->assertPathIs('/buyer/menu');

            // Langkah 1: Klik kategori "Minuman"
            $browser->clickLink('Minuman')
                ->pause(2000) // Tunggu transisi atau muat ulang data kategori
                ->assertPathIs('/buyer/menu');

            // Langkah 2 & Expected Result:
            // 1. Sistem hanya menampilkan produk kategori Minuman
            $browser->assertSee('Es Teh')
                ->assertSee('Jus Alpukat');

            // 2. Produk kategori lain (Makanan) tidak ditampilkan
            $browser->assertDontSee('Nasi Goreng')
                ->assertDontSee('Ayam Bakar');
        });
    }
}