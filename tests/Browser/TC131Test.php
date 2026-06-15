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

class TC131Test extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Setup ekosistem data uji:
     * - Buyer
     * - Seller Aktif (approved) dan Seller Nonaktif (pending)
     * - Kategori "Makanan" dan "Minuman"
     * - Produk Aktif Reguler: "Nasi Goreng" (Makanan), "Ayam Bakar" (Makanan)
     * - Produk Aktif Promo: "Es Teh" (Minuman), "Jus Alpukat" (Minuman)
     * - Produk Nonaktif: "Produk Nonaktif"
     */
    private function setupEcosystem()
    {
        // 1. Buat Buyer
        $buyer = User::firstOrCreate(
            ['email' => 'buyer_tc131@test.com'],
            [
                'name' => 'Buyer TC131',
                'role' => 'buyer',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Buat Seller Aktif (Approved)
        $sellerUserActive = User::firstOrCreate(
            ['email' => 'seller_tc131_active@test.com'],
            [
                'name' => 'Seller TC131 Active',
                'role' => 'seller',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        $sellerActive = Seller::firstOrCreate(
            ['user_id' => $sellerUserActive->id],
            [
                'store_name' => 'Toko Aktif TC131',
                'address' => 'Jl. Aktif No. 131',
                'latitude' => -6.9147,
                'longitude' => 107.6098,
                'verification_status' => 'approved',
            ]
        );

        // 3. Buat Seller Nonaktif (Pending)
        $sellerUserPending = User::firstOrCreate(
            ['email' => 'seller_tc131_pending@test.com'],
            [
                'name' => 'Seller TC131 Pending',
                'role' => 'seller',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        $sellerPending = Seller::firstOrCreate(
            ['user_id' => $sellerUserPending->id],
            [
                'store_name' => 'Toko Pending TC131',
                'address' => 'Jl. Pending No. 131',
                'latitude' => -6.9147,
                'longitude' => 107.6098,
                'verification_status' => 'pending',
            ]
        );

        // 4. Buat Kategori
        $catMakanan = Category::firstOrCreate(['name' => 'Makanan']);
        $catMinuman = Category::firstOrCreate(['name' => 'Minuman']);

        // 5. Buat Produk Aktif Reguler (di Seller Aktif)
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

        // 6. Buat Produk Aktif Promo (di Seller Aktif)
        $esteh = Product::create([
            'seller_id' => $sellerActive->id,
            'category_id' => $catMinuman->id,
            'name' => 'Es Teh',
            'description' => 'Es Teh Promo',
            'base_price' => 4000,
            'image' => 'default.jpg',
        ]);
        Stock::create(['product_id' => $esteh->id, 'qty_reg' => 10, 'qty_surplus' => 5]);
        Discount::create(['product_id' => $esteh->id, 'discount_price' => 2000, 'is_active' => true]);

        $jusalpukat = Product::create([
            'seller_id' => $sellerActive->id,
            'category_id' => $catMinuman->id,
            'name' => 'Jus Alpukat',
            'description' => 'Jus Alpukat Promo',
            'base_price' => 10000,
            'image' => 'default.jpg',
        ]);
        Stock::create(['product_id' => $jusalpukat->id, 'qty_reg' => 10, 'qty_surplus' => 5]);
        Discount::create(['product_id' => $jusalpukat->id, 'discount_price' => 5000, 'is_active' => true]);

        // 7. Buat Produk Nonaktif (di Seller Pending)
        $produkNonaktif = Product::create([
            'seller_id' => $sellerPending->id,
            'category_id' => $catMakanan->id,
            'name' => 'Produk Nonaktif',
            'description' => 'Produk dari seller yang nonaktif',
            'base_price' => 20000,
            'image' => 'default.jpg',
        ]);
        Stock::create(['product_id' => $produkNonaktif->id, 'qty_reg' => 5]);

        return compact('buyer');
    }

    /**
     * TC-13.1: Menguji tampilan seluruh produk pada halaman Daftar Menu.
     */
    public function test_tampilan_seluruh_produk_aktif(): void
    {
        $eco = $this->setupEcosystem();

        $this->browse(function (Browser $browser) use ($eco) {
            // Langkah 1 & 2: Login sebagai Buyer dan buka halaman Daftar Menu
            $browser->loginAs($eco['buyer'])
                ->visit('/buyer/menu')
                ->waitForText('Katalog Menu Sisa Rasa')
                ->assertPathIs('/buyer/menu');

            // Langkah 3 & Expected Result:
            // 1. Seluruh produk aktif tampil
            // 2. Produk Reguler tampil
            $browser->assertSee('Nasi Goreng')
                ->assertSee('Ayam Bakar');

            // 3. Produk Promo tampil
            $browser->assertSee('Es Teh')
                ->pause(5000)
                ->assertSee('Jus Alpukat');

            // 4. Produk nonaktif tidak tampil
            $browser->assertDontSee('Produk Nonaktif');
        });
    }
}