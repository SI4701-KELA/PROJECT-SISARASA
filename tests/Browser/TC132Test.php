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

class TC132Test extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Setup ekosistem data uji:
     * - Buyer
     * - Seller Aktif (approved)
     * - Kategori "Makanan"
     * - Seluruh produk aktif adalah Reguler (tanpa discount):
     *   * "Nasi Goreng", "Ayam Bakar", "Mie Goreng"
     */
    private function setupEcosystem()
    {
        // 1. Buat Buyer
        $buyer = User::firstOrCreate(
            ['email' => 'buyer_tc132@test.com'],
            [
                'name' => 'Buyer TC132',
                'role' => 'buyer',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Buat Seller Aktif (Approved)
        $sellerUserActive = User::firstOrCreate(
            ['email' => 'seller_tc132_active@test.com'],
            [
                'name' => 'Seller TC132 Active',
                'role' => 'seller',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        $sellerActive = Seller::firstOrCreate(
            ['user_id' => $sellerUserActive->id],
            [
                'store_name' => 'Toko Aktif TC132',
                'address' => 'Jl. Aktif No. 132',
                'latitude' => -6.9147,
                'longitude' => 107.6098,
                'verification_status' => 'approved',
            ]
        );

        // 3. Buat Kategori
        $catMakanan = Category::firstOrCreate(['name' => 'Makanan']);

        // 4. Buat Produk Reguler
        $nasigoreng = Product::create([
            'seller_id' => $sellerActive->id,
            'category_id' => $catMakanan->id,
            'name' => 'Nasi Goreng',
            'description' => 'Nasi Goreng Reguler',
            'base_price' => 12000,
            'image' => 'default.jpg',
        ]);
        Stock::create(['product_id' => $nasigoreng->id, 'qty_reg' => 10]);

        $ayambakar = Product::create([
            'seller_id' => $sellerActive->id,
            'category_id' => $catMakanan->id,
            'name' => 'Ayam Bakar',
            'description' => 'Ayam Bakar Reguler',
            'base_price' => 15000,
            'image' => 'default.jpg',
        ]);
        Stock::create(['product_id' => $ayambakar->id, 'qty_reg' => 10]);

        $miegoreng = Product::create([
            'seller_id' => $sellerActive->id,
            'category_id' => $catMakanan->id,
            'name' => 'Mie Goreng',
            'description' => 'Mie Goreng Reguler',
            'base_price' => 10000,
            'image' => 'default.jpg',
        ]);
        Stock::create(['product_id' => $miegoreng->id, 'qty_reg' => 10]);

        return compact('buyer');
    }

    /**
     * TC-13.2: Menguji tampilan produk ketika seluruh produk seller berstatus Reguler.
     */
    public function test_tampilan_produk_reguler_tanpa_promo(): void
    {
        $eco = $this->setupEcosystem();

        $this->browse(function (Browser $browser) use ($eco) {
            // Langkah 1: Login dan buka halaman Daftar Menu
            $browser->loginAs($eco['buyer'])
                ->visit('/buyer/menu')
                ->waitForText('Katalog Menu Sisa Rasa')
                ->assertPathIs('/buyer/menu');

            // Langkah 2 & Expected Result:
            // 1. Semua produk tampil sebagai Reguler
            $browser->assertSee('Nasi Goreng')
                ->assertSee('Ayam Bakar')
                ->assertSee('Mie Goreng');

            // 2. Tidak terdapat badge promo / diskon
            $browser->assertMissing('.promo-badge')
                ->assertDontSee('Promo')
                ->pause(5000)
                ->assertDontSee('Diskon');

            // 3. Elemen harga coret tidak muncul
            $browser->assertMissing('.harga-coret');
        });
    }
}