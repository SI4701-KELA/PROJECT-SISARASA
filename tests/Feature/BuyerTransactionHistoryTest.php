<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerTransactionHistoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to create a buyer, seller, and product.
     */
    private function setupBaseEntities()
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $sellerUser = User::factory()->create(['role' => 'seller']);
        
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Warung Enak',
            'address' => 'Jl. Pahlawan No. 42',
            'verification_status' => 'approved',
        ]);

        $category = Category::create(['name' => 'Makanan']);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Nasi Rames',
            'description' => 'Nasi rames enak',
            'base_price' => 15000,
            'image' => 'products/test.jpg',
        ]);

        return compact('buyer', 'seller', 'product');
    }

    /**
     * TS.PBI.004 [Read] Akses Riwayat Transaksi
     * Menampilkan daftar pesanan yang sudah selesai/dibatalkan, diurutkan DESC.
     */
    public function test_buyer_can_view_their_own_transaction_history_in_descending_order(): void
    {
        $entities = $this->setupBaseEntities();
        $buyer = $entities['buyer'];
        $seller = $entities['seller'];
        $product = $entities['product'];

        // Buat pesanan selesai (lama)
        $orderOld = new Order([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'total_amount' => 15000,
            'payment_method' => 'cash',
            'status' => 'selesai',
        ]);
        $orderOld->id = 888;
        $orderOld->save();
        $orderOld->created_at = now()->subDays(2);
        $orderOld->save();
        $orderOld->items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 15000,
        ]);

        // Buat pesanan dibatalkan (baru)
        $orderNew = new Order([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'total_amount' => 30000,
            'payment_method' => 'cash',
            'status' => 'dibatalkan',
            'created_at' => now(),
        ]);
        $orderNew->id = 999;
        $orderNew->save();
        $orderNew->items()->create([
            'product_id' => $product->id,
            'qty' => 2,
            'price' => 15000,
        ]);

        $response = $this->actingAs($buyer)->get(route('buyer.orders.index', ['tab' => 'riwayat']));

        $response->assertStatus(200);
        $response->assertSee('#' . $orderOld->id);
        $response->assertSee('#' . $orderNew->id);
        $response->assertSee('Warung Enak');
        $response->assertSee('Selesai');
        $response->assertSee('Dibatalkan');

        // Memastikan order yang lebih baru muncul di bagian atas (ordering check)
        $content = $response->getContent();
        $posNew = strpos($content, '#' . $orderNew->id);
        $posOld = strpos($content, '#' . $orderOld->id);
        
        $this->assertTrue($posNew !== false);
        $this->assertTrue($posOld !== false);
        $this->assertTrue($posNew < $posOld, 'Pesanan yang lebih baru harus muncul sebelum pesanan lama.');
    }

    /**
     * Memastikan pesanan aktif tidak muncul di tab "Riwayat Transaksi"
     */
    public function test_active_orders_are_excluded_from_history_tab(): void
    {
        $entities = $this->setupBaseEntities();
        $buyer = $entities['buyer'];
        $seller = $entities['seller'];
        $product = $entities['product'];

        // Pesanan aktif (status: diproses)
        $orderActive = new Order([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'total_amount' => 15000,
            'payment_method' => 'cash',
            'status' => 'diproses',
        ]);
        $orderActive->id = 888;
        $orderActive->save();
        $orderActive->items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 15000,
        ]);

        // Pesanan riwayat (status: selesai)
        $orderHistory = new Order([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'total_amount' => 15000,
            'payment_method' => 'cash',
            'status' => 'selesai',
        ]);
        $orderHistory->id = 999;
        $orderHistory->save();
        $orderHistory->items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 15000,
        ]);

        $response = $this->actingAs($buyer)->get(route('buyer.orders.index', ['tab' => 'riwayat']));

        $response->assertStatus(200);
        $response->assertSee('#' . $orderHistory->id);
        $response->assertDontSee('#' . $orderActive->id);
    }

    /**
     * Kriteria Penerimaan: Akurasi Data Kepemilikan (Pembeli A mutlak tidak dapat melihat milik Pembeli B)
     */
    public function test_buyer_cannot_view_others_transaction_history(): void
    {
        $entities = $this->setupBaseEntities();
        $buyerA = $entities['buyer'];
        $seller = $entities['seller'];
        $product = $entities['product'];

        $buyerB = User::factory()->create(['role' => 'buyer']);

        // Order milik Buyer B
        $orderB = new Order([
            'buyer_id' => $buyerB->id,
            'seller_id' => $seller->id,
            'total_amount' => 15000,
            'payment_method' => 'cash',
            'status' => 'selesai',
        ]);
        $orderB->id = 999;
        $orderB->save();
        $orderB->items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 15000,
        ]);

        // Buyer A melihat halaman riwayatnya
        $response = $this->actingAs($buyerA)->get(route('buyer.orders.index', ['tab' => 'riwayat']));

        $response->assertStatus(200);
        $response->assertDontSee('#' . $orderB->id);
    }

    /**
     * TS.PBI.004 [Read] Tampil Empty State
     * Menampilkan pesan saat riwayat masih kosong.
     */
    public function test_empty_state_rendered_when_no_transactions(): void
    {
        $entities = $this->setupBaseEntities();
        $buyer = $entities['buyer'];

        $response = $this->actingAs($buyer)->get(route('buyer.orders.index', ['tab' => 'riwayat']));

        $response->assertStatus(200);
        $response->assertSee('Belum ada riwayat pesanan');
        $response->assertSee('Anda belum memiliki riwayat pesanan. Mari cari makanan lezat dari toko di sekitar Anda!');
    }

    /**
     * Detail Invoice: Menampilkan seluruh rincian order_items & seller info
     */
    public function test_buyer_can_view_invoice_details(): void
    {
        $entities = $this->setupBaseEntities();
        $buyer = $entities['buyer'];
        $seller = $entities['seller'];
        $product = $entities['product'];

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'total_amount' => 15000,
            'payment_method' => 'cash',
            'status' => 'selesai',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 15000,
            'is_surplus' => true,
        ]);

        $response = $this->actingAs($buyer)->get(route('buyer.orders.show', $order->id));

        $response->assertStatus(200);
        $response->assertSee('Detail Invoice #' . $order->id);
        $response->assertSee('Warung Enak');
        $response->assertSee('Jl. Pahlawan No. 42');
        $response->assertSee('Nasi Rames');
        $response->assertSee('SURPLUS');
        $response->assertSee('Rp 15.000');
    }

    /**
     * Detail Invoice: Keamanan data kepemilikan detail invoice
     */
    public function test_buyer_cannot_view_others_invoice_details(): void
    {
        $entities = $this->setupBaseEntities();
        $buyerA = $entities['buyer'];
        $seller = $entities['seller'];
        $product = $entities['product'];

        $buyerB = User::factory()->create(['role' => 'buyer']);

        // Order milik Buyer B
        $orderB = Order::create([
            'buyer_id' => $buyerB->id,
            'seller_id' => $seller->id,
            'total_amount' => 15000,
            'payment_method' => 'cash',
            'status' => 'selesai',
        ]);
        $orderB->items()->create([
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 15000,
        ]);

        // Buyer A mencoba mengakses invoice milik Buyer B
        $response = $this->actingAs($buyerA)->get(route('buyer.orders.show', $orderB->id));

        // Harus 404 (firstOrFail menyembunyikan eksistensi resource bagi user lain)
        $response->assertStatus(404);
    }
}
