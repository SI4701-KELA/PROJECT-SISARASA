<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Stock;
use App\Models\Cart;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * PBI-15: Mengelola daftar belanjaan di keranjang
 */
class CartTest extends DuskTestCase
{
    use DatabaseTruncation;

    private User $buyer;
    private Seller $seller;
    private Product $productReguler;
    private Product $productSurplus;

    protected function setUp(): void
    {
        parent::setUp();
        
        $category = Category::first();
        if (!$category) {
            $category = Category::create(['name' => 'Makanan Test']);
        }

        $this->buyer = User::factory()->create([
            'name' => 'Buyer Cart',
            'email' => 'buyer_cart_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'buyer',
        ]);

        $sellerUser = User::factory()->create([
            'name' => 'Seller Cart',
            'email' => 'seller_cart_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
        ]);

        $this->seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Toko Cart Test',
            'address' => 'Jl. Cart Test',
            'verification_status' => 'approved',
        ]);

        $this->productReguler = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'name' => 'Item Reguler',
            'base_price' => 15000,
            'image' => 'dummy.jpg',
        ]);
        Stock::create([
            'product_id' => $this->productReguler->id,
            'qty_reg' => 10,
            'qty_surplus' => 0,
        ]);

        $this->productSurplus = Product::create([
            'seller_id' => $this->seller->id,
            'category_id' => $category->id,
            'name' => 'Item Surplus',
            'base_price' => 20000,
            'image' => 'dummy.jpg',
        ]);
        $this->productSurplus->discount()->create([
            'discount_price' => 12000,
            'is_active' => true,
        ]);
        Stock::create([
            'product_id' => $this->productSurplus->id,
            'qty_reg' => 5,
            'qty_surplus' => 3,
        ]);
    }

    /**
     * Memastikan item dengan harga reguler (bukan surplus) dapat masuk dan 
     * ditampilkan dengan benar di halaman keranjang tanpa label promo.
     */
    #[Test]
    #[Group('PBI-15')]
    #[Group('TC-CRT-001')]
    public function test_item_reguler_di_keranjang(): void
    {
        Cart::create([
            'buyer_id' => $this->buyer->id,
            'product_id' => $this->productReguler->id,
            'seller_id' => $this->seller->id,
            'qty' => 1,
            'is_surplus' => false,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->buyer)
                ->visitRoute('buyer.cart')
                ->pause(1000)
                ->assertSee('Item Reguler')
                ->assertSee('Rp 15.000')
                ->assertDontSee('Promo Sisa Rasa');
        });
    }

    /**
     * Memastikan item surplus ditampilkan dengan harga diskon dan label promo khusus 
     * ('Promo Sisa Rasa') di halaman keranjang.
     */
    #[Test]
    #[Group('PBI-15')]
    #[Group('TC-CRT-002')]
    public function test_item_surplus_promo_di_keranjang(): void
    {
        Cart::create([
            'buyer_id' => $this->buyer->id,
            'product_id' => $this->productSurplus->id,
            'seller_id' => $this->seller->id,
            'qty' => 1,
            'is_surplus' => true,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->buyer)
                ->visitRoute('buyer.cart')
                ->pause(1000)
                ->assertSee('Item Surplus')
                ->assertSee('Rp 12.000') // Harga diskon
                ->assertSee('PROMO SISA RASA');
        });
    }

    /**
     * Menguji perhitungan subtotal setiap item berdasarkan harga asli/diskon 
     * dikali kuantitas, dan total tagihan secara keseluruhan.
     */
    #[Test]
    #[Group('PBI-15')]
    #[Group('TC-CRT-003')]
    public function test_kalkulasi_subtotal_dan_total_tagihan(): void
    {
        Cart::create([
            'buyer_id' => $this->buyer->id,
            'product_id' => $this->productReguler->id,
            'seller_id' => $this->seller->id,
            'qty' => 2, // 2 x 15000 = 30000
            'is_surplus' => false,
        ]);
        Cart::create([
            'buyer_id' => $this->buyer->id,
            'product_id' => $this->productSurplus->id,
            'seller_id' => $this->seller->id,
            'qty' => 1, // 1 x 12000 = 12000
            'is_surplus' => true,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->buyer)
                ->visitRoute('buyer.cart')
                ->pause(1000)
                // Subtotal
                ->assertSee('Rp 30.000')
                ->assertSee('Rp 12.000')
                // Total
                ->assertSee('Rp 42.000');
        });
    }

    /**
     * Menguji fungsionalitas tombol increment (+) untuk menambah jumlah item 
     * secara langsung dari halaman keranjang dan memastikan subtotal terupdate.
     */
    #[Test]
    #[Group('PBI-15')]
    #[Group('TC-CRT-004')]
    public function test_tambah_qty_di_keranjang(): void
    {
        Cart::create([
            'buyer_id' => $this->buyer->id,
            'product_id' => $this->productReguler->id,
            'seller_id' => $this->seller->id,
            'qty' => 1,
            'is_surplus' => false,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->buyer)
                ->visitRoute('buyer.cart')
                ->pause(1000)
                ->click('.btn-increment')
                ->pause(1000)
                ->assertSeeIn('.qty-display', '2')
                ->assertSee('Rp 30.000'); // Subtotal berubah real-time
        });
    }

    /**
     * Menguji fungsionalitas tombol decrement (-) untuk mengurangi jumlah item 
     * di halaman keranjang beserta perbaruan harga subtotal.
     */
    #[Test]
    #[Group('PBI-15')]
    #[Group('TC-CRT-005')]
    public function test_kurang_qty_di_keranjang(): void
    {
        Cart::create([
            'buyer_id' => $this->buyer->id,
            'product_id' => $this->productReguler->id,
            'seller_id' => $this->seller->id,
            'qty' => 2,
            'is_surplus' => false,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->buyer)
                ->visitRoute('buyer.cart')
                ->pause(1000)
                ->click('.btn-decrement')
                ->pause(1000)
                ->assertSeeIn('.qty-display', '1')
                ->assertSee('Rp 15.000'); 
        });
    }

    /**
     * Memastikan bahwa kuantitas item yang ditambahkan tidak dapat melebihi 
     * sisa stok yang tersedia dengan menonaktifkan tombol tambah (disabled).
     */
    #[Test]
    #[Group('PBI-15')]
    #[Group('TC-CRT-006')]
    public function test_validasi_sisa_stok_keranjang(): void
    {
        Cart::create([
            'buyer_id' => $this->buyer->id,
            'product_id' => $this->productSurplus->id,
            'seller_id' => $this->seller->id,
            'qty' => 3, // Maksimal stok surplus adalah 3
            'is_surplus' => true,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->buyer)
                ->visitRoute('buyer.cart')
                ->pause(1000);
            
            // Tombol tambah harus disabled
            $disabled = $browser->driver->findElement(\Facebook\WebDriver\WebDriverBy::cssSelector('.btn-increment'))->getAttribute('disabled');
            $this->assertNotEmpty($disabled, 'Tombol increment tidak didisable saat stok maksimal tercapai');
        });
    }

    /**
     * Menguji fungsionalitas tombol hapus untuk menghapus item secara permanen 
     * dari daftar belanja keranjang.
     */
    #[Test]
    #[Group('PBI-15')]
    #[Group('TC-CRT-007')]
    public function test_hapus_item_dari_keranjang(): void
    {
        Cart::create([
            'buyer_id' => $this->buyer->id,
            'product_id' => $this->productReguler->id,
            'seller_id' => $this->seller->id,
            'qty' => 1,
            'is_surplus' => false,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->buyer)
                ->visitRoute('buyer.cart')
                ->pause(1000)
                ->click('.btn-delete')
                ->pause(1500)
                ->assertSee('Keranjang Anda Kosong');
        });
    }

    /**
     * Menguji tampilan "Keranjang Kosong" ketika tidak ada item dan tombol 
     * redirect kembali ke halaman menu/katalog produk.
     */
    #[Test]
    #[Group('PBI-15')]
    #[Group('TC-CRT-008')]
    public function test_tampilan_keranjang_kosong(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->buyer)
                ->visitRoute('buyer.cart')
                ->pause(1000)
                ->assertSee('Keranjang Anda Kosong')
                ->clickLink('Cari Makanan Lainnya')
                ->pause(1000)
                ->assertRouteIs('buyer.menu'); // Redirect ke halaman Katalog/Menu
        });
    }
}