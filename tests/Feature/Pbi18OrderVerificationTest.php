<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi18OrderVerificationTest extends TestCase
{
    use RefreshDatabase;

    private function createEcosystem(array $overrides = []): array
    {
        $sellerUser = User::factory()->create([
            'role' => 'seller',
        ]);

        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => $overrides['store_name'] ?? 'Toko Test A',
            'address' => 'Jl. Test No.1',
            'verification_status' => 'approved',
            'qris_image' => 'qris/test.png',
        ]);

        $buyer = User::factory()->create([
            'role' => 'buyer',
            'phone' => '081234567890',
        ]);

        $category = Category::create([
            'name' => 'Makanan',
        ]);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Nasi Goreng',
            'description' => 'Nasi goreng spesial',
            'base_price' => 25000,
            'image' => 'products/test.jpg',
        ]);

        Stock::create([
            'product_id' => $product->id,
            'qty_reg' => 10,
            'qty_surplus' => 5,
        ]);

        return compact('sellerUser', 'seller', 'buyer', 'product');
    }

    private function createOrder(array $eco, array $overrides = []): Order
    {
        $order = Order::create([
            'buyer_id' => $eco['buyer']->id,
            'seller_id' => $eco['seller']->id,
            'total_amount' => 50000,
            'payment_method' => 'cash',
            'status' => $overrides['status'] ?? 'siap_diambil',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $eco['product']->id,
            'qty' => 2,
            'price' => 25000,
            'is_surplus' => false,
        ]);

        return $order;
    }

    /**
     * Test: Generator QR Code / Pickup Code otomatis dibuat dengan awalan SISA- dan 5 alfanumerik.
     */
    public function test_pickup_code_is_automatically_generated_on_order_creation(): void
    {
        $eco = $this->createEcosystem();
        $order = $this->createOrder($eco);

        $this->assertNotEmpty($order->pickup_code);
        $this->assertStringStartsWith('SISA-', $order->pickup_code);
        $this->assertEquals(10, strlen($order->pickup_code));
    }

    /**
     * Test: Tampilan QR Code di halaman sukses/lacak jika status 'siap_diambil'.
     */
    public function test_qr_code_and_text_shown_when_order_is_ready_for_pickup(): void
    {
        $eco = $this->createEcosystem();
        $order = $this->createOrder($eco, ['status' => 'siap_diambil']);

        $response = $this->actingAs($eco['buyer'])
            ->get(route('buyer.checkout.success', $order->id));

        $response->assertStatus(200);
        $response->assertSee('Tunjukkan QR Code ini ke Penjual');
        $response->assertSee($order->pickup_code);
        $response->assertSee('qrcode-canvas');
    }

    /**
     * Test: Verifikasi Berhasil dengan Kode Penuh.
     */
    public function test_seller_can_verify_pickup_with_full_code(): void
    {
        $eco = $this->createEcosystem();
        $order = $this->createOrder($eco, ['status' => 'siap_diambil']);

        $response = $this->actingAs($eco['sellerUser'])
            ->postJson(route('seller.orders.verify'), [
                'pickup_code' => $order->pickup_code,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Pesanan Berhasil Diserahkan'
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'selesai',
        ]);
    }

    /**
     * Test: Verifikasi Berhasil dengan 5 digit Akhir.
     */
    public function test_seller_can_verify_pickup_with_five_digit_suffix(): void
    {
        $eco = $this->createEcosystem();
        $order = $this->createOrder($eco, ['status' => 'siap_diambil']);
        
        // Dapatkan 5 digit akhir, misal "SISA-8XF9Q" -> "8XF9Q"
        $suffix = substr($order->pickup_code, 5);

        $response = $this->actingAs($eco['sellerUser'])
            ->postJson(route('seller.orders.verify'), [
                'pickup_code' => $suffix,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'selesai',
        ]);
    }

    /**
     * Test: Validasi error jika Kode Salah.
     */
    public function test_verification_fails_with_invalid_code(): void
    {
        $eco = $this->createEcosystem();
        $this->createOrder($eco, ['status' => 'siap_diambil']);

        $response = $this->actingAs($eco['sellerUser'])
            ->postJson(route('seller.orders.verify'), [
                'pickup_code' => 'SISA-WRONG',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Kode tidak valid!'
        ]);
    }

    /**
     * Test Keamanan: Penjual A tidak dapat memverifikasi pesanan milik Penjual B.
     */
    public function test_seller_a_cannot_verify_seller_b_order(): void
    {
        // Setup Seller A
        $ecoA = $this->createEcosystem();
        $orderA = $this->createOrder($ecoA, ['status' => 'siap_diambil']);

        // Setup Seller B
        $sellerUserB = User::factory()->create(['role' => 'seller']);
        Seller::create([
            'user_id' => $sellerUserB->id,
            'store_name' => 'Toko Test B',
            'address' => 'Jl. Test B No.2',
            'verification_status' => 'approved',
        ]);

        // Seller B mencoba memverifikasi Order A
        $response = $this->actingAs($sellerUserB)
            ->postJson(route('seller.orders.verify'), [
                'pickup_code' => $orderA->pickup_code,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Kode tidak valid!'
        ]);

        // Status pesanan A harus tetap siap_diambil
        $this->assertDatabaseHas('orders', [
            'id' => $orderA->id,
            'status' => 'siap_diambil',
        ]);
    }
}
