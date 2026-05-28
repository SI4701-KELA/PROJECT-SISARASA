<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: membuat ekosistem data lengkap (buyer, seller, product, stock, discount, cart).
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
            'qris_image' => $overrides['qris_image'] ?? null,
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

        // Tambahkan item ke keranjang
        $cart = Cart::create([
            'buyer_id' => $buyer->id,
            'product_id' => $product->id,
            'qty' => $overrides['cart_qty'] ?? 2,
            'is_surplus' => $overrides['cart_is_surplus'] ?? false,
        ]);

        return compact('buyer', 'sellerUser', 'seller', 'category', 'product', 'stock', 'discount', 'cart');
    }

    // =========================================================================
    // TC-PAY-001: Memastikan UI hanya menyediakan opsi Cash dan QRIS
    // =========================================================================
    public function test_checkout_page_shows_cash_and_qris_payment_options(): void
    {
        $eco = $this->createEcosystem();

        $response = $this->actingAs($eco['buyer'])->get(route('buyer.checkout'));

        $response->assertStatus(200);
        // Harus menampilkan kedua opsi pembayaran
        $response->assertSee('Cash');
        $response->assertSee('QRIS');
        // Harus ada radio button dengan value cash dan qris
        $response->assertSee('value="cash"', false);
        $response->assertSee('value="qris"', false);
        // Harus menampilkan ringkasan pesanan
        $response->assertSee('Ringkasan Pesanan');
        $response->assertSee('Metode Pembayaran');
    }

    // =========================================================================
    // TC-PAY-002: Menguji keakuratan tampilan Barcode QRIS milik toko (Dinamis)
    // =========================================================================
    public function test_qris_barcode_displayed_dynamically_per_seller(): void
    {
        Storage::fake('public');

        // Toko A dengan QRIS
        $ecoA = $this->createEcosystem([
            'store_name' => 'Toko A',
            'qris_image' => 'qris/toko_a.png',
            'category_name' => 'Makanan A',
        ]);

        $responseA = $this->actingAs($ecoA['buyer'])->get(route('buyer.checkout'));
        $responseA->assertStatus(200);
        // Harus menampilkan QRIS milik Toko A
        $responseA->assertSee('QRIS Toko A');
        $responseA->assertSee('qris/toko_a.png', false);

        // Toko B dengan QRIS berbeda — buyer berbeda
        $buyerB = User::factory()->create(['role' => 'buyer']);
        $sellerUserB = User::factory()->create(['role' => 'seller']);
        $sellerB = Seller::create([
            'user_id' => $sellerUserB->id,
            'store_name' => 'Toko B',
            'address' => 'Jl. B No.2',
            'verification_status' => 'approved',
            'qris_image' => 'qris/toko_b.png',
        ]);
        $categoryB = Category::create(['name' => 'Makanan B']);
        $productB = Product::create([
            'seller_id' => $sellerB->id,
            'category_id' => $categoryB->id,
            'name' => 'Mie Ayam',
            'description' => 'Mie ayam spesial',
            'base_price' => 20000,
            'image' => 'products/test_b.jpg',
        ]);
        Stock::create(['product_id' => $productB->id, 'qty_reg' => 10, 'qty_surplus' => 5]);
        Discount::create(['product_id' => $productB->id, 'discount_price' => 12000, 'is_active' => true]);
        Cart::create([
            'buyer_id' => $buyerB->id,
            'product_id' => $productB->id,
            'qty' => 1,
            'is_surplus' => false,
        ]);

        $responseB = $this->actingAs($buyerB)->get(route('buyer.checkout'));
        $responseB->assertStatus(200);
        // Harus menampilkan QRIS milik Toko B (berbeda dari Toko A)
        $responseB->assertSee('QRIS Toko B');
        $responseB->assertSee('qris/toko_b.png', false);
        $responseB->assertDontSee('qris/toko_a.png', false);
    }

    // =========================================================================
    // TC-PAY-003: Menguji penanganan jika toko belum punya QRIS
    // =========================================================================
    public function test_qris_unavailable_shows_warning_when_seller_has_no_qris(): void
    {
        $eco = $this->createEcosystem([
            'qris_image' => null, // Toko TIDAK punya QRIS
        ]);

        $response = $this->actingAs($eco['buyer'])->get(route('buyer.checkout'));

        $response->assertStatus(200);
        // Harus menampilkan pesan bahwa QRIS belum tersedia
        $response->assertSee('QRIS Belum Tersedia');
        $response->assertSee('Toko ini belum mengatur pembayaran QRIS');
    }

    // =========================================================================
    // TC-PAY-003b: Backend menolak QRIS jika toko belum punya barcode QRIS
    // =========================================================================
    public function test_qris_payment_rejected_when_seller_has_no_qris_image(): void
    {
        Storage::fake('public');

        $eco = $this->createEcosystem([
            'qris_image' => null,
        ]);

        $fakeProof = UploadedFile::fake()->image('bukti.jpg', 200, 200);

        $response = $this->actingAs($eco['buyer'])->post(route('buyer.checkout.store'), [
            'payment_method' => 'qris',
            'payment_proof' => $fakeProof,
        ]);

        // Harus redirect kembali dengan error
        $response->assertRedirect();
        $response->assertSessionHasErrors('payment_method');

        // Tidak ada order yang dibuat
        $this->assertDatabaseCount('orders', 0);
    }

    // =========================================================================
    // TC-PAY-004: Menguji alur metode pembayaran Cash
    // =========================================================================
    public function test_cash_payment_creates_order_with_diproses_status(): void
    {
        $eco = $this->createEcosystem([
            'base_price' => 25000,
            'cart_qty' => 2,
            'cart_is_surplus' => false,
        ]);

        $response = $this->actingAs($eco['buyer'])->post(route('buyer.checkout.store'), [
            'payment_method' => 'cash',
        ]);

        // Harus redirect ke halaman sukses
        $response->assertRedirect();

        // Order harus dibuat
        $order = Order::where('buyer_id', $eco['buyer']->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('cash', $order->payment_method);
        $this->assertEquals('diproses', $order->status);
        $this->assertNull($order->payment_proof);
        $this->assertEquals(50000, $order->total_amount); // 25000 * 2

        // Order items harus ada
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $eco['product']->id,
            'qty' => 2,
            'price' => 25000,
            'is_surplus' => false,
        ]);

        // Keranjang harus kosong
        $this->assertDatabaseMissing('carts', [
            'buyer_id' => $eco['buyer']->id,
        ]);
    }

    // =========================================================================
    // TC-PAY-005: Menguji keberhasilan alur QRIS + upload bukti transfer
    // =========================================================================
    public function test_qris_payment_with_proof_creates_order_with_menunggu_verifikasi(): void
    {
        Storage::fake('public');

        $eco = $this->createEcosystem([
            'base_price' => 25000,
            'discount_price' => 15000,
            'cart_qty' => 3,
            'cart_is_surplus' => true,
            'qris_image' => 'qris/toko_test.png',
        ]);

        $fakeProof = UploadedFile::fake()->image('bukti_transfer.jpg', 400, 600);

        $response = $this->actingAs($eco['buyer'])->post(route('buyer.checkout.store'), [
            'payment_method' => 'qris',
            'payment_proof' => $fakeProof,
        ]);

        $response->assertRedirect();

        // Order harus dibuat
        $order = Order::where('buyer_id', $eco['buyer']->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('qris', $order->payment_method);
        $this->assertEquals('menunggu_verifikasi', $order->status);
        $this->assertNotNull($order->payment_proof);
        $this->assertEquals(45000, $order->total_amount); // 15000 * 3 (surplus price)

        // Bukti transfer harus tersimpan di storage
        Storage::disk('public')->assertExists($order->payment_proof);

        // Order items harus punya is_surplus = true dan harga diskon
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $eco['product']->id,
            'qty' => 3,
            'price' => 15000,
            'is_surplus' => true,
        ]);

        // Keranjang harus kosong
        $this->assertDatabaseMissing('carts', [
            'buyer_id' => $eco['buyer']->id,
        ]);
    }

    // =========================================================================
    // TC-PAY-005b: QRIS tanpa upload bukti transfer → ditolak
    // =========================================================================
    public function test_qris_payment_rejected_without_proof_upload(): void
    {
        $eco = $this->createEcosystem([
            'qris_image' => 'qris/toko_test.png',
        ]);

        $response = $this->actingAs($eco['buyer'])->post(route('buyer.checkout.store'), [
            'payment_method' => 'qris',
            // TANPA payment_proof
        ]);

        // Harus redirect kembali dengan error validasi
        $response->assertRedirect();
        $response->assertSessionHasErrors('payment_proof');

        // Tidak ada order yang dibuat
        $this->assertDatabaseCount('orders', 0);

        // Keranjang TIDAK terhapus
        $this->assertDatabaseHas('carts', [
            'buyer_id' => $eco['buyer']->id,
        ]);
    }

    // =========================================================================
    // TC-PAY-005c: QRIS upload file non-image → ditolak
    // =========================================================================
    public function test_qris_payment_rejected_with_non_image_file(): void
    {
        Storage::fake('public');

        $eco = $this->createEcosystem([
            'qris_image' => 'qris/toko_test.png',
        ]);

        $fakeFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->actingAs($eco['buyer'])->post(route('buyer.checkout.store'), [
            'payment_method' => 'qris',
            'payment_proof' => $fakeFile,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('payment_proof');

        $this->assertDatabaseCount('orders', 0);
    }

    // =========================================================================
    // TC-PAY-006: Menguji keamanan API — payment_method selain cash/qris ditolak
    // =========================================================================
    public function test_invalid_payment_method_rejected_by_backend(): void
    {
        $eco = $this->createEcosystem();

        // Coba "transfer_bank" → harus ditolak
        $response1 = $this->actingAs($eco['buyer'])->post(route('buyer.checkout.store'), [
            'payment_method' => 'transfer_bank',
        ]);
        $response1->assertRedirect();
        $response1->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('orders', 0);

        // Coba kosong → harus ditolak
        $response2 = $this->actingAs($eco['buyer'])->post(route('buyer.checkout.store'), [
            'payment_method' => '',
        ]);
        $response2->assertRedirect();
        $response2->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('orders', 0);

        // Coba tanpa field payment_method → harus ditolak
        $response3 = $this->actingAs($eco['buyer'])->post(route('buyer.checkout.store'), []);
        $response3->assertRedirect();
        $response3->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('orders', 0);
    }

    // =========================================================================
    // Additional: Keranjang otomatis kosong setelah checkout
    // =========================================================================
    public function test_cart_is_empty_after_successful_checkout(): void
    {
        $eco = $this->createEcosystem();

        // Pastikan keranjang ada sebelum checkout
        $this->assertDatabaseHas('carts', ['buyer_id' => $eco['buyer']->id]);

        $this->actingAs($eco['buyer'])->post(route('buyer.checkout.store'), [
            'payment_method' => 'cash',
        ]);

        // Setelah checkout, keranjang harus kosong
        $this->assertDatabaseMissing('carts', ['buyer_id' => $eco['buyer']->id]);

        // Akses halaman keranjang → empty state
        $response = $this->actingAs($eco['buyer'])->get(route('buyer.cart'));
        $response->assertStatus(200);
        $response->assertSee('Keranjang Anda Kosong');
    }

    // =========================================================================
    // Additional: Redirect ke halaman sukses yang benar
    // =========================================================================
    public function test_successful_checkout_redirects_to_success_page(): void
    {
        $eco = $this->createEcosystem();

        $response = $this->actingAs($eco['buyer'])->post(route('buyer.checkout.store'), [
            'payment_method' => 'cash',
        ]);

        $order = Order::where('buyer_id', $eco['buyer']->id)->first();
        $response->assertRedirect(route('buyer.checkout.success', $order->id));

        // Akses halaman sukses
        $successResponse = $this->actingAs($eco['buyer'])->get(route('buyer.checkout.success', $order->id));
        $successResponse->assertStatus(200);
        $successResponse->assertSee('#' . $order->id);
    }

    // =========================================================================
    // Additional: Checkout redirect jika keranjang kosong
    // =========================================================================
    public function test_checkout_redirects_when_cart_is_empty(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);

        $response = $this->actingAs($buyer)->get(route('buyer.checkout'));

        $response->assertRedirect(route('buyer.cart'));
    }

    // =========================================================================
    // Additional: Upload file terlalu besar (>2MB) ditolak
    // =========================================================================
    public function test_qris_payment_rejected_when_file_exceeds_2mb(): void
    {
        Storage::fake('public');

        $eco = $this->createEcosystem([
            'qris_image' => 'qris/toko_test.png',
        ]);

        // File 3MB => melebihi batas 2MB
        $largeFile = UploadedFile::fake()->image('large_proof.jpg')->size(3000);

        $response = $this->actingAs($eco['buyer'])->post(route('buyer.checkout.store'), [
            'payment_method' => 'qris',
            'payment_proof' => $largeFile,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('payment_proof');
        $this->assertDatabaseCount('orders', 0);
    }

    // =========================================================================
    // Additional: Stok terpotong setelah checkout reguler
    // =========================================================================
    public function test_stock_deducted_after_regular_checkout(): void
    {
        $eco = $this->createEcosystem([
            'base_price' => 20000,
            'cart_qty' => 3,
            'cart_is_surplus' => false,
            'qty_reg' => 10,
        ]);

        $this->actingAs($eco['buyer'])->post(route('buyer.checkout.store'), [
            'payment_method' => 'cash',
        ]);

        // Stok reguler harus berkurang dari 10 → 7
        $eco['stock']->refresh();
        $this->assertEquals(7, $eco['stock']->qty_reg);
    }

    // =========================================================================
    // Additional: Stok surplus terpotong setelah checkout surplus
    // =========================================================================
    public function test_stock_deducted_after_surplus_checkout(): void
    {
        Storage::fake('public');

        $eco = $this->createEcosystem([
            'discount_price' => 15000,
            'cart_qty' => 2,
            'cart_is_surplus' => true,
            'qty_surplus' => 5,
            'qris_image' => 'qris/toko_test.png',
        ]);

        $fakeProof = UploadedFile::fake()->image('bukti.jpg', 200, 200);

        $this->actingAs($eco['buyer'])->post(route('buyer.checkout.store'), [
            'payment_method' => 'qris',
            'payment_proof' => $fakeProof,
        ]);

        // Stok surplus harus berkurang dari 5 → 3
        $eco['stock']->refresh();
        $this->assertEquals(3, $eco['stock']->qty_surplus);
    }

    // =========================================================================
    // Additional: Halaman sukses menampilkan informasi pesanan yang benar
    // =========================================================================
    public function test_success_page_shows_order_details(): void
    {
        $eco = $this->createEcosystem([
            'base_price' => 25000,
            'cart_qty' => 2,
            'cart_is_surplus' => false,
        ]);

        $this->actingAs($eco['buyer'])->post(route('buyer.checkout.store'), [
            'payment_method' => 'cash',
        ]);

        $order = Order::where('buyer_id', $eco['buyer']->id)->first();

        $response = $this->actingAs($eco['buyer'])
            ->get(route('buyer.checkout.success', $order->id));

        $response->assertStatus(200);
        $response->assertSee('#' . $order->id);
        $response->assertSee('Diproses'); // Status cash = diproses
        $response->assertSee('cash');
        $response->assertSee('Rp 50.000'); // 25000 * 2
    }

    // =========================================================================
    // Additional: Halaman sukses QRIS menampilkan status Menunggu Verifikasi
    // =========================================================================
    public function test_success_page_shows_menunggu_verifikasi_for_qris(): void
    {
        Storage::fake('public');

        $eco = $this->createEcosystem([
            'base_price' => 25000,
            'discount_price' => 15000,
            'cart_qty' => 1,
            'cart_is_surplus' => true,
            'qris_image' => 'qris/toko_test.png',
        ]);

        $fakeProof = UploadedFile::fake()->image('bukti.jpg', 200, 200);

        $this->actingAs($eco['buyer'])->post(route('buyer.checkout.store'), [
            'payment_method' => 'qris',
            'payment_proof' => $fakeProof,
        ]);

        $order = Order::where('buyer_id', $eco['buyer']->id)->first();

        $response = $this->actingAs($eco['buyer'])
            ->get(route('buyer.checkout.success', $order->id));

        $response->assertStatus(200);
        $response->assertSee('Menunggu Verifikasi');
        $response->assertSee('qris');
    }
}

