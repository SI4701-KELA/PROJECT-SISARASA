<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class TC134Test extends DuskTestCase
{
    /**
     * TC-13.4: Menguji sistem konfirmasi penghapusan produk (Delete).
     * 
     * Skenario: Memastikan klik tombol hapus memunculkan pop-up JS confirm() 
     * dan mencegah penghapusan langsung saat dialog di-dismiss (Cancel).
     * 
     * CATATAN TEKNIS:
     * Chrome dalam mode headless (--headless=new) otomatis men-dismiss semua
     * native dialog (confirm/alert) tanpa pernah muncul sebagai "alert" yang
     * bisa ditangkap oleh WebDriver/Dusk (waitForDialog/assertDialogOpened).
     * 
     * Solusi: Kita "override" window.confirm() dengan JavaScript spy SEBELUM
     * tombol diklik. Spy ini merekam apakah confirm() dipanggil dan pesan apa
     * yang ditampilkan, lalu mengembalikan false (simulasi klik "Batal").
     * Setelah klik, kita assert hasil rekaman spy dari PHP.
     */
    public function test_konfirmasi_hapus_produk_muncul(): void
    {
        // ===================================================================
        // TAHAP 1: SETUP DATA — Suntikkan 1 produk dummy ke database
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

        // Buat produk dummy baru
        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Produk Dummy Untuk Dihapus',
            'description' => 'Ini adalah deskripsi produk dummy.',
            'base_price' => 15000,
            'image' => 'products/dummy.jpg',
        ]);

        // Buat stok dummy agar view tidak error saat merender qty
        Stock::create([
            'product_id' => $product->id,
            'qty_reg' => 10,
            'qty_surplus' => 5,
        ]);

        // ===================================================================
        // TAHAP 2: BROWSER TEST
        // ===================================================================
        $this->browse(function (Browser $browser) use ($product) {
            // 2. Login sebagai penjual (Seller)
            $browser->visit('/login') 
                ->waitFor('input[type="email"]', 5) 
                ->type('input[type="email"]', 'uiop@gmail.com') 
                ->type('input[type="password"]', 'uiopuiop') 
                ->press('Login') 
                ->pause(2000)
                
                // 3. Navigasi ke Halaman Katalog Produk
                ->visit('/seller/products') 
                ->pause(2000)
                
                // Pastikan produk dummy terlihat di halaman
                ->assertSee('Produk Dummy Untuk Dihapus');

            // =============================================================
            // 4. PASANG SPY pada window.confirm() SEBELUM klik tombol Hapus
            // =============================================================
            // Spy ini akan:
            //   - Merekam bahwa confirm() dipanggil (__confirmCalled = true)
            //   - Merekam pesan yang ditampilkan (__confirmMessage = '...')
            //   - Mengembalikan false (simulasi user menekan "Batal/Cancel")
            //     sehingga produk TIDAK benar-benar terhapus dari database
            $browser->script("
                window.__confirmCalled = false;
                window.__confirmMessage = '';
                window.confirm = function(msg) {
                    window.__confirmCalled = true;
                    window.__confirmMessage = msg;
                    return false; // simulasi klik Cancel
                };
            ");

            // =============================================================
            // 5. KLIK TOMBOL "Hapus" via JavaScript
            // =============================================================
            // Gunakan requestSubmit() yang men-trigger onsubmit handler.
            // Karena spy sudah mengganti confirm() jadi non-blocking,
            // script() tidak akan hang.
            $browser->script("document.querySelector('form.inline').requestSubmit()");
            $browser->pause(1000);

            // =============================================================
            // 6. VALIDASI UTAMA
            // =============================================================
            // Assert 1: confirm() memang dipanggil (pop-up peringatan muncul)
            $confirmCalled = $browser->script("return window.__confirmCalled");
            $this->assertTrue(
                $confirmCalled[0], 
                'Sistem harus memanggil confirm() saat tombol Hapus diklik.'
            );

            // Assert 2: Pesan di dalam confirm() sesuai dengan yang ada di blade
            $confirmMessage = $browser->script("return window.__confirmMessage");
            $this->assertEquals(
                'Apakah Anda yakin ingin menghapus produk ini?',
                $confirmMessage[0],
                'Pesan konfirmasi tidak sesuai dengan yang diharapkan.'
            );

            // Assert 3: Produk MASIH terlihat di halaman (tidak terhapus karena Cancel)
            $browser->assertSee('Produk Dummy Untuk Dihapus');
        });
    }
}