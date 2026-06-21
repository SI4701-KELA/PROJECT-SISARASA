<?php

namespace Tests\Browser;

use App\Models\Product;
use App\Models\Category;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * PBI-31: Tuning Website
 */
class TCOPT003Test extends DuskTestCase
{
    use DatabaseTruncation;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->buyer = User::factory()->create([
            'name' => 'Buyer Test',
            'email' => 'buyer_opt_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'buyer',
        ]);
    }

    /**
     * Memastikan fitur pagination (pembagian halaman) berjalan dengan semestinya 
     * saat data katalog produk dalam jumlah besar (>20 data).
     */
    #[Test]
    #[Group('PBI-31')]
    #[Group('TC-OPT-003')]
    public function test_implementasi_pagination_data_volume_besar(): void
    {
        $category = Category::first();
        if (!$category) {
            $category = Category::create(['name' => 'Makanan Test']);
        }

        $sellerUser = User::factory()->create(['role' => 'seller']);
        $seller = Seller::create(['user_id' => $sellerUser->id, 'store_name' => 'Toko Besar', 'verification_status' => 'approved']);

        // Buat 50+ data dummy
        for ($i = 1; $i <= 50; $i++) {
            Product::create([
                'seller_id' => $seller->id,
                'category_id' => Category::first()->id,
                'name' => 'Produk Dummy ' . $i,
                'base_price' => 10000,
                'image' => 'dummy.jpg',
            ]);
        }

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->buyer)
                ->visitRoute('buyer.menu')
                ->pause(1000);
            
            // Verifikasi tombol pagination muncul di UI
            $this->assertNotEmpty($browser->driver->findElements(\Facebook\WebDriver\WebDriverBy::cssSelector('nav[role="navigation"]')));
        });
    }
}
