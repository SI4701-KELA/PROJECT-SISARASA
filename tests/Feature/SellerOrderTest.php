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

class SellerOrderTest extends TestCase
{
    use RefreshDatabase;


    private function createEcosystem(array $overrides = []): array
    {
        $sellerUser = User::factory()->create([
            'role' => 'seller',
        ]);

        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => $overrides['store_name'] ?? 'Toko Test',
            'address' => 'Jl. Test No.1',
            'verification_status' => 'approved',
            'qris_image' => $overrides['qris_image'] ?? 'qris/test.png',
        ]);

        $buyer = User::factory()->create([
            'role' => 'buyer',
            'phone' => '081234567890',
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

        Stock::create([
            'product_id' => $product->id,
            'qty_reg' => 10,
            'qty_surplus' => 5,
        ]);

        Discount::create([
            'product_id' => $product->id,
            'discount_price' => $overrides['discount_price'] ?? 15000,
            'is_active' => true,
        ]);

        return compact('sellerUser', 'seller', 'buyer', 'category', 'product');
    }


    private function createOrder(array $eco, array $overrides = []): Order
    {
        $price = $overrides['price'] ?? $eco['product']->base_price;
        $qty = $overrides['qty'] ?? 2;

        $order = Order::create([
            'buyer_id' => $eco['buyer']->id,
            'seller_id' => $eco['seller']->id,
            'total_amount' => $price * $qty,
            'payment_method' => $overrides['payment_method'] ?? 'qris',
            'payment_proof' => $overrides['payment_proof'] ?? 'payments/bukti.jpg',
            'status' => $overrides['status'] ?? 'menunggu_verifikasi',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $eco['product']->id,
            'qty' => $qty,
            'price' => $price,
            'is_surplus' => $overrides['is_surplus'] ?? false,
        ]);

        return $order;
    }


    public function test_seller_can_only_see_own_orders(): void
    {
        $eco = $this->createEcosystem(['store_name' => 'Toko A']);
        $orderA = $this->createOrder($eco);

        $sellerUserB = User::factory()->create(['role' => 'seller']);
        $sellerB = Seller::create([
            'user_id' => $sellerUserB->id,
            'store_name' => 'Toko B',
            'address' => 'Jl. B No.2',
            'verification_status' => 'approved',
        ]);

        $responseA = $this->actingAs($eco['sellerUser'])->get(route('seller.orders'));
        $responseA->assertStatus(200);
        $responseA->assertSee('#' . $orderA->id);

        $responseB = $this->actingAs($sellerUserB)->get(route('seller.orders'));
        $responseB->assertStatus(200);
        $responseB->assertDontSee('#' . $orderA->id);
    }


    public function test_tab_baru_shows_menunggu_verifikasi_orders(): void
    {
        $eco = $this->createEcosystem();
        $orderBaru = $this->createOrder($eco, ['status' => 'menunggu_verifikasi']);
        $orderDiproses = $this->createOrder($eco, ['status' => 'diproses', 'payment_method' => 'cash', 'payment_proof' => null]);

        $response = $this->actingAs($eco['sellerUser'])->get(route('seller.orders', ['tab' => 'baru']));

        $response->assertStatus(200);
        $response->assertSee('#' . $orderBaru->id);
        $response->assertDontSee('#' . $orderDiproses->id);
    }

    public function test_tab_diproses_shows_diproses_orders(): void
    {
        $eco = $this->createEcosystem();
        $orderBaru = $this->createOrder($eco, ['status' => 'menunggu_verifikasi']);
        $orderDiproses = $this->createOrder($eco, ['status' => 'diproses', 'payment_method' => 'cash', 'payment_proof' => null]);

        $response = $this->actingAs($eco['sellerUser'])->get(route('seller.orders', ['tab' => 'diproses']));

        $response->assertStatus(200);
        $response->assertDontSee('#' . $orderBaru->id);
        $response->assertSee('#' . $orderDiproses->id);
    }


    public function test_tab_siap_shows_siap_diambil_orders(): void
    {
        $eco = $this->createEcosystem();
        $orderSiap = $this->createOrder($eco, ['status' => 'siap_diambil']);

        $response = $this->actingAs($eco['sellerUser'])->get(route('seller.orders', ['tab' => 'siap']));

        $response->assertStatus(200);
        $response->assertSee('#' . $orderSiap->id);
    }


    public function test_payment_proof_button_shown_for_qris_orders(): void
    {
        $eco = $this->createEcosystem();
        $this->createOrder($eco, [
            'payment_method' => 'qris',
            'payment_proof' => 'payments/bukti.jpg',
            'status' => 'menunggu_verifikasi',
        ]);

        $response = $this->actingAs($eco['sellerUser'])->get(route('seller.orders', ['tab' => 'baru']));

        $response->assertStatus(200);
        $response->assertSee('Lihat Bukti Transfer');
        $response->assertSee('payments/bukti.jpg', false);
    }


    public function test_accept_payment_changes_status_to_diproses(): void
    {
        $eco = $this->createEcosystem();
        $order = $this->createOrder($eco, ['status' => 'menunggu_verifikasi']);

        $response = $this->actingAs($eco['sellerUser'])
            ->patch(route('seller.orders.accept', $order->id));

        $response->assertRedirect(route('seller.orders', ['tab' => 'diproses']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'diproses',
        ]);
    }

    public function test_reject_payment_changes_status_to_dibatalkan_with_reason(): void
    {
        $eco = $this->createEcosystem();
        $order = $this->createOrder($eco, ['status' => 'menunggu_verifikasi']);

        $response = $this->actingAs($eco['sellerUser'])
            ->patch(route('seller.orders.reject', $order->id), [
                'cancellation_reason' => 'Toko tutup',
            ]);

        $response->assertRedirect(route('seller.orders', ['tab' => 'baru']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'dibatalkan',
            'cancellation_reason' => 'Toko tutup',
        ]);
    }


    public function test_reject_payment_requires_cancellation_reason(): void
    {
        $eco = $this->createEcosystem();
        $order = $this->createOrder($eco, ['status' => 'menunggu_verifikasi']);

        $response = $this->actingAs($eco['sellerUser'])
            ->patch(route('seller.orders.reject', $order->id), [
                'cancellation_reason' => '',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('cancellation_reason');

        // Status harus tetap menunggu_verifikasi
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'menunggu_verifikasi',
        ]);
    }


    public function test_reject_payment_requires_valid_dropdown_reason(): void
    {
        $eco = $this->createEcosystem();
        $order = $this->createOrder($eco, ['status' => 'menunggu_verifikasi']);

        $response = $this->actingAs($eco['sellerUser'])
            ->patch(route('seller.orders.reject', $order->id), [
                'cancellation_reason' => 'Alasan sembarang',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('cancellation_reason');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'menunggu_verifikasi',
        ]);
    }


    public function test_mark_ready_changes_status_to_siap_diambil(): void
    {
        $eco = $this->createEcosystem();
        $order = $this->createOrder($eco, [
            'status' => 'diproses',
            'payment_method' => 'cash',
            'payment_proof' => null,
        ]);

        $response = $this->actingAs($eco['sellerUser'])
            ->patch(route('seller.orders.ready', $order->id));

        $response->assertRedirect(route('seller.orders', ['tab' => 'siap']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'siap_diambil',
        ]);
    }


    public function test_seller_cannot_accept_other_sellers_order(): void
    {
        $eco = $this->createEcosystem();
        $order = $this->createOrder($eco, ['status' => 'menunggu_verifikasi']);

        $sellerUserB = User::factory()->create(['role' => 'seller']);
        Seller::create([
            'user_id' => $sellerUserB->id,
            'store_name' => 'Toko B',
            'address' => 'Jl. B No.2',
            'verification_status' => 'approved',
        ]);

        $response = $this->actingAs($sellerUserB)
            ->patch(route('seller.orders.accept', $order->id));

        $response->assertStatus(404);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'menunggu_verifikasi',
        ]);
    }


    public function test_seller_cannot_reject_other_sellers_order(): void
    {
        $eco = $this->createEcosystem();
        $order = $this->createOrder($eco, ['status' => 'menunggu_verifikasi']);

        $sellerUserB = User::factory()->create(['role' => 'seller']);
        Seller::create([
            'user_id' => $sellerUserB->id,
            'store_name' => 'Toko B',
            'address' => 'Jl. B No.2',
            'verification_status' => 'approved',
        ]);

        $response = $this->actingAs($sellerUserB)
            ->patch(route('seller.orders.reject', $order->id), [
                'cancellation_reason' => 'Toko tutup',
            ]);

        $response->assertStatus(404);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'menunggu_verifikasi',
        ]);
    }


    public function test_cannot_accept_order_not_in_menunggu_verifikasi(): void
    {
        $eco = $this->createEcosystem();
        $order = $this->createOrder($eco, [
            'status' => 'diproses',
            'payment_method' => 'cash',
            'payment_proof' => null,
        ]);

        $response = $this->actingAs($eco['sellerUser'])
            ->patch(route('seller.orders.accept', $order->id));

        $response->assertStatus(404);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'diproses',
        ]);
    }

    public function test_cannot_mark_ready_order_not_in_diproses(): void
    {
        $eco = $this->createEcosystem();
        $order = $this->createOrder($eco, ['status' => 'menunggu_verifikasi']);

        $response = $this->actingAs($eco['sellerUser'])
            ->patch(route('seller.orders.ready', $order->id));

        $response->assertStatus(404);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'menunggu_verifikasi',
        ]);
    }


    public function test_order_items_displayed_with_surplus_badge(): void
    {
        $eco = $this->createEcosystem();
        $order = $this->createOrder($eco, [
            'status' => 'menunggu_verifikasi',
            'is_surplus' => true,
            'price' => 15000,
        ]);

        $response = $this->actingAs($eco['sellerUser'])->get(route('seller.orders', ['tab' => 'baru']));

        $response->assertStatus(200);
        $response->assertSee('SURPLUS');
        $response->assertSee('Nasi Goreng');
    }


    public function test_cancelled_order_shows_cancellation_reason_in_history(): void
    {
        $eco = $this->createEcosystem();
        $order = $this->createOrder($eco, ['status' => 'menunggu_verifikasi']);

        $this->actingAs($eco['sellerUser'])
            ->patch(route('seller.orders.reject', $order->id), [
                'cancellation_reason' => 'Stok habis',
            ]);

        $response = $this->actingAs($eco['sellerUser'])->get(route('seller.orders', ['tab' => 'selesai']));

        $response->assertStatus(200);
        $response->assertSee('Stok habis');
        $response->assertSee('Dibatalkan');
    }
}
