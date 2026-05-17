<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: membuat ekosistem data lengkap (buyer, seller, product, stock, discount).
     */
    private function createEcosystem(array $overrides = []): array
    {
        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $sellerUser = User::factory()->create([
            'role' => 'seller',
        ]);

        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => $overrides['store_name'] ?? 'Toko Test',
            'address' => 'Jl. Test No.1',
            'verification_status' => 'approved',
        ]);

        $category = Category::create([
            'name' => $overrides['category_name'] ?? 'Makanan',
        ]);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => $overrides['product_name'] ?? 'Nasi Goreng',
            'description' => 'Nasi goreng spesial',
            'base_price' => $overrides['base_price'] ?? 25000,
            'image' => 'products/test.jpg',
        ]);

        $stock = Stock::create([
            'product_id' => $product->id,
            'qty_reg' => $overrides['qty_reg'] ?? 10,
            'qty_surplus' => $overrides['qty_surplus'] ?? 5,
        ]);

        $discount = Discount::create([
            'product_id' => $product->id,
            'discount_price' => $overrides['discount_price'] ?? 15000,
            'is_active' => $overrides['is_active'] ?? true,
        ]);

        return compact('buyer', 'sellerUser', 'seller', 'category', 'product', 'stock', 'discount');
    }

    // =========================================================================
    // TC-CRT-001: Menguji tampilan item reguler di dalam keranjang
    // =========================================================================
    public function test_regular_item_displayed_with_base_price(): void
    {
        $eco = $this->createEcosystem([
            'base_price' => 25000,
        ]);

        // Insert item reguler (is_surplus = false)
        Cart::create([
            'buyer_id' => $eco['buyer']->id,
            'product_id' => $eco['product']->id,
            'qty' => 2,
            'is_surplus' => false,
        ]);

        $response = $this->actingAs($eco['buyer'])->get(route('buyer.cart'));

        $response->assertStatus(200);
        // Harga base_price harus muncul
        $response->assertSee('Rp 25.000');
        // Tidak boleh ada badge surplus
        $response->assertDontSee('Promo Sisa Rasa');
    }

    // =========================================================================
    // TC-CRT-002: Menguji tampilan item surplus/promo di dalam keranjang
    // =========================================================================
    public function test_surplus_item_displayed_with_discount_price_and_badge(): void
    {
        $eco = $this->createEcosystem([
            'base_price' => 25000,
            'discount_price' => 15000,
        ]);

        // Insert item surplus (is_surplus = true)
        Cart::create([
            'buyer_id' => $eco['buyer']->id,
            'product_id' => $eco['product']->id,
            'qty' => 1,
            'is_surplus' => true,
        ]);

        $response = $this->actingAs($eco['buyer'])->get(route('buyer.cart'));

        $response->assertStatus(200);
        // Harga discount_price harus muncul
        $response->assertSee('Rp 15.000');
        // Badge "Promo Sisa Rasa" harus ada
        $response->assertSee('Promo Sisa Rasa');
    }

    // =========================================================================
    // TC-CRT-003: Menguji keakuratan kalkulasi Subtotal dan Total Tagihan
    // =========================================================================
    public function test_subtotal_and_total_calculation_accuracy(): void
    {
        $eco1 = $this->createEcosystem([
            'product_name' => 'Nasi Goreng',
            'base_price' => 20000,
            'discount_price' => 12000,
            'store_name' => 'Toko A',
            'category_name' => 'Makanan Berat',
        ]);

        // Item reguler: 20000 * 3 = 60000
        Cart::create([
            'buyer_id' => $eco1['buyer']->id,
            'product_id' => $eco1['product']->id,
            'qty' => 3,
            'is_surplus' => false,
        ]);

        // Buat produk kedua di bawah seller yang sama
        $product2 = Product::create([
            'seller_id' => $eco1['seller']->id,
            'category_id' => $eco1['category']->id,
            'name' => 'Mie Ayam',
            'description' => 'Mie ayam spesial',
            'base_price' => 18000,
            'image' => 'products/test2.jpg',
        ]);

        Stock::create([
            'product_id' => $product2->id,
            'qty_reg' => 10,
            'qty_surplus' => 5,
        ]);

        Discount::create([
            'product_id' => $product2->id,
            'discount_price' => 10000,
            'is_active' => true,
        ]);

        // Item surplus: 10000 * 2 = 20000
        Cart::create([
            'buyer_id' => $eco1['buyer']->id,
            'product_id' => $product2->id,
            'qty' => 2,
            'is_surplus' => true,
        ]);

        $response = $this->actingAs($eco1['buyer'])->get(route('buyer.cart'));

        $response->assertStatus(200);

        // View data check: grandTotal = 60000 + 20000 = 80000
        $response->assertViewHas('grandTotal', 80000);

        // Subtotals harus muncul di view
        $response->assertSee('Rp 60.000'); // Subtotal item 1
        $response->assertSee('Rp 20.000'); // Subtotal item 2
        // Total: Rp 80.000
        $response->assertSee('Rp 80.000');
    }

    // =========================================================================
    // TC-CRT-004: Menguji fungsionalitas penambahan jumlah porsi (Qty)
    // =========================================================================
    public function test_increment_qty_updates_cart_and_recalculates(): void
    {
        $eco = $this->createEcosystem([
            'base_price' => 25000,
            'qty_reg' => 10,
        ]);

        $cart = Cart::create([
            'buyer_id' => $eco['buyer']->id,
            'product_id' => $eco['product']->id,
            'qty' => 2,
            'is_surplus' => false,
        ]);

        // Kirim PATCH: qty dari 2 → 3
        $response = $this->actingAs($eco['buyer'])
            ->patchJson(route('buyer.cart.update', $cart->id), [
                'qty' => 3,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'qty' => 3,
            'subtotal' => 75000, // 25000 * 3
        ]);

        // Pastikan database ter-update
        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'qty' => 3,
        ]);
    }

    // =========================================================================
    // TC-CRT-005: Menguji fungsionalitas pengurangan jumlah porsi (Qty)
    // =========================================================================
    public function test_decrement_qty_updates_cart_and_recalculates(): void
    {
        $eco = $this->createEcosystem([
            'base_price' => 25000,
        ]);

        $cart = Cart::create([
            'buyer_id' => $eco['buyer']->id,
            'product_id' => $eco['product']->id,
            'qty' => 3,
            'is_surplus' => false,
        ]);

        // Kirim PATCH: qty dari 3 → 2
        $response = $this->actingAs($eco['buyer'])
            ->patchJson(route('buyer.cart.update', $cart->id), [
                'qty' => 2,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'qty' => 2,
            'subtotal' => 50000, // 25000 * 2
        ]);

        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'qty' => 2,
        ]);

        // Coba kurangi sampai qty = 1 (batas minimum)
        $response2 = $this->actingAs($eco['buyer'])
            ->patchJson(route('buyer.cart.update', $cart->id), [
                'qty' => 1,
            ]);

        $response2->assertStatus(200);
        $response2->assertJson(['qty' => 1]);

        // Coba qty = 0 → harus ditolak
        $response3 = $this->actingAs($eco['buyer'])
            ->patchJson(route('buyer.cart.update', $cart->id), [
                'qty' => 0,
            ]);

        $response3->assertStatus(422);
        $response3->assertJson(['error' => 'Jumlah minimal adalah 1.']);
    }

    // =========================================================================
    // TC-CRT-006: Menguji validasi sisa stok saat menambah Qty di keranjang
    // =========================================================================
    public function test_increment_qty_rejected_when_stock_exceeded(): void
    {
        $eco = $this->createEcosystem([
            'base_price' => 25000,
            'qty_reg' => 3, // Stok reguler hanya 3
        ]);

        $cart = Cart::create([
            'buyer_id' => $eco['buyer']->id,
            'product_id' => $eco['product']->id,
            'qty' => 3, // Sudah di batas maksimal
            'is_surplus' => false,
        ]);

        // Coba tambah qty ke 4 → melebihi stok
        $response = $this->actingAs($eco['buyer'])
            ->patchJson(route('buyer.cart.update', $cart->id), [
                'qty' => 4,
            ]);

        $response->assertStatus(422);
        $response->assertJson(['error' => 'Stok tidak mencukupi.']);

        // Qty di database harus tetap 3
        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'qty' => 3,
        ]);
    }

    // =========================================================================
    // TC-CRT-006b: Validasi stok surplus saat menambah Qty
    // =========================================================================
    public function test_increment_surplus_qty_rejected_when_stock_exceeded(): void
    {
        $eco = $this->createEcosystem([
            'discount_price' => 15000,
            'qty_surplus' => 2, // Stok surplus hanya 2
        ]);

        $cart = Cart::create([
            'buyer_id' => $eco['buyer']->id,
            'product_id' => $eco['product']->id,
            'qty' => 2,
            'is_surplus' => true,
        ]);

        // Coba tambah ke 3 → melebihi stok surplus
        $response = $this->actingAs($eco['buyer'])
            ->patchJson(route('buyer.cart.update', $cart->id), [
                'qty' => 3,
            ]);

        $response->assertStatus(422);
        $response->assertJson(['error' => 'Stok tidak mencukupi.']);

        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'qty' => 2,
        ]);
    }

    // =========================================================================
    // TC-CRT-007: Menguji fungsionalitas hapus item dari keranjang (Delete)
    // =========================================================================
    public function test_delete_item_removes_from_cart(): void
    {
        $eco = $this->createEcosystem();

        $cart = Cart::create([
            'buyer_id' => $eco['buyer']->id,
            'product_id' => $eco['product']->id,
            'qty' => 2,
            'is_surplus' => false,
        ]);

        $this->assertDatabaseHas('carts', ['id' => $cart->id]);

        $response = $this->actingAs($eco['buyer'])
            ->deleteJson(route('buyer.cart.destroy', $cart->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Pastikan item sudah terhapus dari database
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }

    // =========================================================================
    // TC-CRT-008: Menguji tampilan Empty State ketika keranjang kosong
    // =========================================================================
    public function test_empty_cart_shows_empty_state(): void
    {
        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        // Tidak ada item di carts

        $response = $this->actingAs($buyer)->get(route('buyer.cart'));

        $response->assertStatus(200);
        // Harus menampilkan teks keranjang kosong
        $response->assertSee('Keranjang Anda Kosong');
        // Harus ada tombol/link "Cari Makanan Lainnya"
        $response->assertSee('Cari Makanan Lainnya');
        // Tombol harus mengarah ke route buyer.menu
        $response->assertSee(route('buyer.menu'));
    }

    // =========================================================================
    // Additional: Buyer tidak bisa mengakses cart milik buyer lain
    // =========================================================================
    public function test_buyer_cannot_update_other_buyers_cart(): void
    {
        $eco = $this->createEcosystem();

        $otherBuyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $cart = Cart::create([
            'buyer_id' => $eco['buyer']->id,
            'product_id' => $eco['product']->id,
            'qty' => 2,
            'is_surplus' => false,
        ]);

        // otherBuyer mencoba update cart milik buyer lain
        $response = $this->actingAs($otherBuyer)
            ->patchJson(route('buyer.cart.update', $cart->id), [
                'qty' => 5,
            ]);

        $response->assertStatus(404);

        // Qty tetap 2
        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'qty' => 2,
        ]);
    }

    // =========================================================================
    // Additional: Buyer tidak bisa menghapus cart milik buyer lain
    // =========================================================================
    public function test_buyer_cannot_delete_other_buyers_cart(): void
    {
        $eco = $this->createEcosystem();

        $otherBuyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $cart = Cart::create([
            'buyer_id' => $eco['buyer']->id,
            'product_id' => $eco['product']->id,
            'qty' => 1,
            'is_surplus' => false,
        ]);

        $response = $this->actingAs($otherBuyer)
            ->deleteJson(route('buyer.cart.destroy', $cart->id));

        $response->assertStatus(404);

        // Item masih ada
        $this->assertDatabaseHas('carts', ['id' => $cart->id]);
    }
}
