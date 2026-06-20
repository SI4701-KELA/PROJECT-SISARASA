<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Discount;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;

class TC136Test extends DuskTestCase
{
    use DatabaseTruncation;
    protected $seed = true;
    /**
     * TC-13.6: Menguji format tampilan mata uang (Rupiah).
     * 
     * Skenario: 
     * 1. Berada di halaman Katalog Produk.
     * 2. Periksa nominal angka pada teks Harga Normal dan Harga Diskon.
     * 
     * Expected: Seluruh angka dirender dengan format mata uang yang tepat,
     * menggunakan awalan "Rp" dan pemisah ribuan (contoh: Rp 25.000).
     */
    public function test_format_tampilan_mata_uang_rupiah(): void
    {
        // ===================================================================
        // TAHAP 1: SETUP DATA — Suntikkan produk dummy dengan harga yang diketahui
        // ===================================================================
        $user = User::where('email', 'uiop@gmail.com')->first();
        
        if (!$user) {
            $user = User::create([
                'name' => 'Seller Uiop',
                'email' => 'uiop@gmail.com',
                'password' => bcrypt('uiopuiop'),
                'role' => 'seller',
                'email_verified_at' => now(),
            ]);
        }

        $seller = $user->seller;
        if (!$seller) {
            $seller = \App\Models\Seller::create([
                'user_id' => $user->id,
                'store_name' => 'Toko Uiop',
                'address' => 'Jl. Test No. 123',
                'latitude' => -6.9147,
                'longitude' => 107.6098,
                'verification_status' => 'approved',
            ]);
        }

        $category = Category::first();
        if (!$category) {
            $category = Category::create(['name' => 'Makanan Berat']);
        }

        // Hapus SEMUA produk lama dari seller ini agar bersih
        Product::where('seller_id', $seller->id)->delete();

        // Buat produk dummy dengan harga yang sudah diketahui
        // base_price = 25000 → harus dirender sebagai "Rp 25.000"
        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Nasi Goreng Spesial',
            'description' => 'Produk dummy untuk tes format Rupiah.',
            'base_price' => 25000,
            'image' => 'products/dummy.jpg',
        ]);

        // Buat stok dummy
        Stock::create([
            'product_id' => $product->id,
            'qty_reg' => 10,
            'qty_surplus' => 5,
        ]);

        // Buat diskon dummy: discount_price = 18000 → harus dirender sebagai "Rp 18.000"
        Discount::create([
            'product_id' => $product->id,
            'discount_price' => 18000,
            'is_active' => false,
        ]);

        // ===================================================================
        // TAHAP 2: BROWSER TEST
        // ===================================================================
        $this->browse(function (Browser $browser) use ($user) {
            // 1. Login sebagai penjual (Seller) dan Navigasi ke Halaman Katalog Produk
            $browser->loginAs($user)
                ->visit('/seller/products') 
                ->waitForText('Nasi Goreng Spesial', 5);

            // =============================================================
            // 3. VALIDASI FORMAT HARGA NORMAL
            // =============================================================
            // Harga Normal base_price=25000 harus tampil sebagai "Rp 25.000"
            $browser->assertSee('Rp 25.000');

            // =============================================================
            // 4. VALIDASI FORMAT HARGA DISKON
            // =============================================================
            // Harga Diskon discount_price=18000 harus tampil sebagai "Rp 18.000"
            $browser->assertSee('Rp 18.000');

            // =============================================================
            // 5. VALIDASI TAMBAHAN: Format Rupiah menggunakan regex via JS
            // =============================================================
            // Ambil semua teks harga dari halaman dan pastikan semuanya
            // mengikuti pola "Rp" + spasi + angka dengan pemisah ribuan titik
            $result = $browser->script("
                var priceElements = document.querySelectorAll('.font-bold');
                var prices = [];
                priceElements.forEach(function(el) {
                    var text = el.textContent.trim();
                    if (text.startsWith('Rp')) {
                        prices.push(text);
                    }
                });
                return prices;
            ");

            $prices = $result[0];
            $this->assertNotEmpty($prices, 'Harus ada minimal 1 harga yang ditampilkan di halaman.');

            // Validasi setiap harga mengikuti format "Rp X.XXX" atau "Rp XX.XXX" dst.
            // Pattern: "Rp" + spasi + angka dengan pemisah ribuan titik
            $rupiahPattern = '/^Rp\s[\d]{1,3}(\.[\d]{3})*$/';
            foreach ($prices as $price) {
                $this->assertMatchesRegularExpression(
                    $rupiahPattern,
                    $price,
                    "Harga '{$price}' tidak mengikuti format Rupiah yang benar (contoh: Rp 25.000)."
                );
            }
        });
    }
}
