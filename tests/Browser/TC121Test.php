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

class TC121Test extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Setup ekosistem data uji:
     * - 1 Buyer
     * - 1 Seller Aktif (approved) dan 1 Seller Nonaktif (pending)
     * - 3 Kategori berbeda
     * - 3 Produk Aktif (pada seller aktif) dari kategori berbeda
     * - 1 Produk Inaktif (pada seller pending)
     */
    private function setupEcosystem()
    {
        // 1. Buat Buyer
        $buyer = User::firstOrCreate(
            ['email' => 'buyer_tc121@test.com'],
            [
                'name' => 'Buyer TC121',
                'role' => 'buyer',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Buat Seller Aktif (Approved)
        $sellerUserActive = User::firstOrCreate(
            ['email' => 'seller_active@test.com'],
            [
                'name' => 'Seller Active',
                'role' => 'seller',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        $sellerActive = Seller::firstOrCreate(
            ['user_id' => $sellerUserActive->id],
            [
                'store_name' => 'Toko Aktif Utama',
                'address' => 'Jl. Toko Aktif No. 1',
                'latitude' => -6.9147,
                'longitude' => 107.6098,
                'verification_status' => 'approved',
            ]
        );

        // 3. Buat Seller Nonaktif (Pending)
        $sellerUserPending = User::firstOrCreate(
            ['email' => 'seller_pending@test.com'],
            [
                'name' => 'Seller Pending',
                'role' => 'seller',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );
        $sellerPending = Seller::firstOrCreate(
            ['user_id' => $sellerUserPending->id],
            [
                'store_name' => 'Toko Pending Utama',
                'address' => 'Jl. Toko Pending No. 2',
                'latitude' => -6.9147,
                'longitude' => 107.6098,
                'verification_status' => 'pending',
            ]
        );

        // 4. Buat Kategori
        $catMakanan = Category::firstOrCreate(['name' => 'Makanan Berat']);
        $catCemilan = Category::firstOrCreate(['name' => 'Cemilan & Pastry']);
        $catMinuman = Category::firstOrCreate(['name' => 'Minuman']);

        // 5. Buat 3 Produk Aktif (Seller Approved)
        $product1 = Product::create([
            'seller_id' => $sellerActive->id,
            'category_id' => $catMakanan->id,
            'name' => 'Nasi Goreng Spesial',
            'description' => 'Nasi goreng enak',
            'base_price' => 15000,
            'image' => 'default.jpg',
        ]);
        Stock::create(['product_id' => $product1->id, 'qty_reg' => 10]);

        $product2 = Product::create([
            'seller_id' => $sellerActive->id,
            'category_id' => $catCemilan->id,
            'name' => 'Roti Bakar Cokelat',
            'description' => 'Roti bakar lezat',
            'base_price' => 10000,
            'image' => 'default.jpg',
        ]);
        Stock::create(['product_id' => $product2->id, 'qty_reg' => 15]);

        $product3 = Product::create([
            'seller_id' => $sellerActive->id,
            'category_id' => $catMinuman->id,
            'name' => 'Es Teh Manis',
            'description' => 'Es teh segar',
            'base_price' => 4000,
            'image' => 'default.jpg',
        ]);
        Stock::create(['product_id' => $product3->id, 'qty_reg' => 20]);

        // 6. Buat 1 Produk Inaktif (Seller Pending)
        $product4 = Product::create([
            'seller_id' => $sellerPending->id,
            'category_id' => $catMakanan->id,
            'name' => 'Bakso Super Pedas',
            'description' => 'Bakso pedas',
            'base_price' => 18000,
            'image' => 'default.jpg',
        ]);
        Stock::create(['product_id' => $product4->id, 'qty_reg' => 5]);

        return compact('buyer');
    }

    /**
     * TC-12.1: Menguji tampilan awal (default) halaman Daftar Menu.
     */
    public function test_tampilan_awal_daftar_menu(): void
    {
        $eco = $this->setupEcosystem();

        $this->browse(function (Browser $browser) use ($eco) {
            // Langkah 1 & 2: Login sebagai Buyer dan klik navigasi Daftar Menu
            $browser->loginAs($eco['buyer'])
                ->visit('/buyer/menu')
                ->waitForText('Katalog Menu Sisa Rasa')
                ->assertPathIs('/buyer/menu');

            // Langkah 3: Perhatikan kategori yang aktif (Semua Makanan harus aktif secara default)
            // Tombol "Semua Makanan" memiliki class "bg-[#c04b36]" ketika aktif secara default
            $activeClass = $browser->script("
                let links = Array.from(document.querySelectorAll('a'));
                let allFoodLink = links.find(l => l.textContent.trim() === 'Semua Makanan');
                return allFoodLink ? allFoodLink.className : '';
            ")[0];

            $this->assertStringContainsString('bg-[#c04b36]', $activeClass, 'Kategori "Semua Makanan" tidak aktif secara default.');

            // Langkah 4: Perhatikan daftar produk yang ditampilkan
            // Assertion 1: Seluruh produk aktif muncul di halaman
            $browser->assertSee('Nasi Goreng Spesial')
                ->assertSee('Roti Bakar Cokelat')
                ->pause(5000)
                ->assertSee('Es Teh Manis');

            // Assertion 2: Produk nonaktif (Bakso Super Pedas) tidak boleh tampil
            $browser->assertDontSee('Bakso Super Pedas');
        });
    }
}