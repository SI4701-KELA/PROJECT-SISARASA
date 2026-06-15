<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Discount;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC133Test extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Setup ekosistem data uji:
     * - Buyer
     * - Seller Aktif (approved)
     * - Kategori "Makanan" dan "Minuman"
     * - Seluruh produk memiliki promo aktif:
     *   * "Nasi Goreng Promo" (Makanan), "Ayam Bakar Promo" (Makanan), "Es Teh Promo" (Minuman)
     */
    private function setupEcosystem()
    {
        // 1. Buat Buyer
        $buyer = User::firstOrCreate(
            ['email' => 'buyer_tc133@test.com'],
            [
                'name' => 'Buyer TC133',
                'role' => 'buyer',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Buat Seller Aktif (Approved)
        $sellerUserActive = User::firstOrCreate(
            ['email' => 'seller_tc133_active@test.com'],
            [
                'name' => 'Seller TC133 Active',
                'role' => 'seller',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        $sellerActive = Seller::firstOrCreate(
            ['user_id' => $sellerUserActive->id],
            [
                'store_name' => 'Toko Aktif TC133',
                'address' => 'Jl. Aktif No. 133',
                'latitude' => -6.9147,
                'longitude' => 107.6098,
                'verification_status' => 'approved',
            ]
        );

        // 3. Buat Kategori
        $catMakanan = Category::firstOrCreate(['name' => 'Makanan']);
        $catMinuman = Category::firstOrCreate(['name' => 'Minuman']);

        // 4. Buat Produk dengan Promo Aktif
        $nasigoreng = Product::create([
            'seller_id' => $sellerActive->id,
            'category_id' => $catMakanan->id,
            'name' => 'Nasi Goreng Promo',
            'description' => 'Nasi Goreng Promo',
            'base_price' => 15000,
            'image' => 'default.jpg',
        ]);
        Stock::create(['product_id' => $nasigoreng->id, 'qty_reg' => 10, 'qty_surplus' => 5]);
        Discount::create(['product_id' => $nasigoreng->id, 'discount_price' => 10000, 'is_active' => true]);

        $ayambakar = Product::create([
            'seller_id' => $sellerActive->id,
            'category_id' => $catMakanan->id,
            'name' => 'Ayam Bakar Promo',
            'description' => 'Ayam Bakar Promo',
            'base_price' => 18000,
            'image' => 'default.jpg',
        ]);
        Stock::create(['product_id' => $ayambakar->id, 'qty_reg' => 10, 'qty_surplus' => 5]);
        Discount::create(['product_id' => $ayambakar->id, 'discount_price' => 12000, 'is_active' => true]);

        $esteh = Product::create([
            'seller_id' => $sellerActive->id,
            'category_id' => $catMinuman->id,
            'name' => 'Es Teh Promo',
            'description' => 'Es Teh Promo',
            'base_price' => 4000,
            'image' => 'default.jpg',
        ]);
        Stock::create(['product_id' => $esteh->id, 'qty_reg' => 10, 'qty_surplus' => 5]);
        Discount::create(['product_id' => $esteh->id, 'discount_price' => 2000, 'is_active' => true]);

        return compact('buyer');
    }

    /**
     * TC-13.3: Menguji tampilan produk ketika seluruh produk seller berstatus Promo.
     */
    public function test_tampilan_produk_promo_aktif(): void
    {
        $eco = $this->setupEcosystem();

        $this->browse(function (Browser $browser) use ($eco) {
            // Langkah 1: Login dan buka halaman Daftar Menu
            $browser->loginAs($eco['buyer'])
                ->visit('/buyer/menu')
                ->waitForText('Katalog Menu Sisa Rasa')
                ->assertPathIs('/buyer/menu');

            // Langkah 2 & Expected Result:
            // 1. Seluruh produk tampil
            $browser->assertSee('Nasi Goreng Promo')
                ->assertSee('Ayam Bakar Promo')
                ->assertSee('Es Teh Promo');

            // 2. Terdapat indikator/badge promo
            $browser->assertPresent('.promo-badge');

            // 3. Terdapat harga diskon (misal Rp 10.000, Rp 12.000, Rp 2.000)
            $browser->assertSee('Rp 10.000')
                ->assertSee('Rp 12.000')
                ->pause(5000)
                ->assertSee('Rp 2.000');

            // 4. Terdapat harga normal/coret
            $browser->assertPresent('.harga-coret');
        });
    }
}