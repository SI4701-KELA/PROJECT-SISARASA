<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Base class untuk semua test PBI-20 (Manajemen Komplain).
 * Menyimpan helper factory agar setiap file TC tetap bersih.
 */
abstract class ComplaintTestCase extends DuskTestCase
{
    use DatabaseTruncation;

    // ─── Factory Helpers ─────────────────────────────────────────

    protected function createBuyer(string $name, string $email): User
    {
        return User::factory()->create([
            'name'     => $name,
            'email'    => $email,
            'role'     => 'buyer',
            'password' => bcrypt('password'),
        ]);
    }

    protected function createAdmin(): User
    {
        return User::factory()->create([
            'name'     => 'Admin Test',
            'email'    => 'admin_complaint@admin.com',
            'role'     => 'admin',
            'password' => bcrypt('password'),
        ]);
    }

    protected function createSellerAndStore(): Seller
    {
        $sellerUser = User::factory()->create([
            'name'     => 'Seller Complaint',
            'email'    => 'seller_complaint_' . uniqid() . '@seller.com',
            'role'     => 'seller',
            'password' => bcrypt('password'),
        ]);

        return Seller::create([
            'user_id'             => $sellerUser->id,
            'store_name'          => 'Toko Complaint',
            'address'             => 'Jl. Test No. 1',
            'verification_status' => 'approved',
        ]);
    }

    protected function createOrder(User $buyer, Seller $seller, string $status): Order
    {
        $order = Order::create([
            'buyer_id'       => $buyer->id,
            'seller_id'      => $seller->id,
            'status'         => $status,
            'total_amount'   => 50000,
            'payment_method' => 'cash',
        ]);

        $category = Category::firstOrCreate(['name' => 'Makanan Test']);

        $product = Product::create([
            'seller_id'   => $seller->id,
            'category_id' => $category->id,
            'name'        => 'Produk Test',
            'base_price'  => 50000,
            'image'       => 'dummy.jpg',
        ]);

        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'qty'        => 1,
            'price'      => 50000,
        ]);

        return $order;
    }

    protected function loginAs(Browser $browser, User $user): Browser
    {
        return $browser->loginAs($user);
    }
}
