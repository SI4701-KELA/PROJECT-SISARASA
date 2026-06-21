<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Category;

class PBI14PortionSelectionTC01Test extends DuskTestCase
{
/**
     * TC-POR-001: Menguji penambahan jumlah porsi (Positive)
     */
    public function test_increment_portion(): void
    {
        $user = $this->getOrCreateBuyer();
        $product = $this->getOrCreateProduct();

        $this->browse(function (Browser $browser) use ($user, $product): void {
            $browser->loginAs($user)
                ->visit('/buyer/menu')
                ->waitForText($product->name, 10)
                ->assertSeeIn('.flex-1.bg-white span', '0') // Porsi awal = 0
                ->click('.flex-1.bg-white button:nth-child(3)') // Klik [+]
                ->assertSeeIn('.flex-1.bg-white span', '1'); // Bertambah menjadi 1
        });
    }
/**
     * Helper: Mendapatkan atau membuat user buyer
     */
    private function getOrCreateBuyer(): User
    {
        return User::where('role', 'buyer')->first() 
            ?? User::factory()->create(['role' => 'buyer']);
    }

    /**
     * Helper: Mendapatkan produk pertama atau membuat satu produk dummy jika kosong
     */
    private function getOrCreateProduct(): Product
    {
        // Pastikan produk yang dikembalikan memiliki record stock dengan qty_reg > 0
        $product = Product::whereHas('stock', function($q) {
            $q->where('qty_reg', '>', 0);
        })->with('stock')->first();

        if ($product) {
            return $product;
        }

        // Jika database kosong, buat produk dummy dengan stok = 5
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Warung Nasi Budi',
            'verification_status' => 'approved',
            'address' => 'Jl. Sudirman No. 12, Bandung',
            'latitude' => -6.9147,
            'longitude' => 107.6098,
        ]);

        $category = Category::firstOrCreate(['name' => 'Makanan Berat']);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Nasi Goreng Rendang',
            'description' => 'Nasi goreng lezat dengan bumbu rendang asli',
            'base_price' => 15000,
            'image' => 'products/dummy.jpg',
        ]);

        $product->stock()->create([
            'qty_reg' => 5,
            'qty_surplus' => 0,
        ]);

        return $product->load('stock');
    }
}
