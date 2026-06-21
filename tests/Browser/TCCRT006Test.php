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
class TCCRT006Test extends DuskTestCase
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
}
