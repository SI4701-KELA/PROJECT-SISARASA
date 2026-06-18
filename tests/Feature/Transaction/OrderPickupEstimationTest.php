<?php

namespace Tests\Feature\Transaction;

use App\Models\Order;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderPickupEstimationTest extends TestCase
{
    use RefreshDatabase;

    private function createBuyer()
    {
        return User::create([
            'name' => 'Test Buyer',
            'email' => 'buyer' . rand() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'buyer'
        ]);
    }

    private function createSeller($closeTime = '23:00:00')
    {
        $user = User::create([
            'name' => 'Test Seller',
            'email' => 'seller' . rand() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'seller'
        ]);

        return Seller::create([
            'user_id' => $user->id,
            'store_name' => 'Toko Ujian',
            'close_time' => $closeTime
        ]);
    }

    private function createOrder($buyerId, $sellerId, $status = 'diproses', $pickupDeadline = null)
    {
        return Order::create([
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
            'total_amount' => 10000,
            'payment_method' => 'cash',
            'status' => $status,
            'pickup_deadline' => $pickupDeadline
        ]);
    }

    public function test_eta_is_shown_when_order_processing()
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $order = $this->createOrder($buyer->id, $seller->id, 'diproses');

        $response = $this->actingAs($buyer)->get(route('buyer.checkout.success', $order->id));

        $response->assertStatus(200);
        $response->assertSee('Pesanan sedang disiapkan. Estimasi waktu penyiapan:');
        $response->assertSee('15-20 Menit');
    }

    public function test_deadline_is_shown_when_order_ready()
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        $deadline = Carbon::now()->addHours(2);
        $order = $this->createOrder($buyer->id, $seller->id, 'siap_diambil', $deadline);

        $response = $this->actingAs($buyer)->get(route('buyer.checkout.success', $order->id));

        $response->assertStatus(200);
        $response->assertSee('Harap ambil pesanan Anda dalam waktu:');
        $response->assertSee('(Batas Maksimal: ' . $deadline->timezone('Asia/Jakarta')->format('H:i') . ' WIB)');
    }

    public function test_deadline_calculation_with_default_duration()
    {
        $sellerUser = User::create([
            'name' => 'Test Seller',
            'email' => 'seller' . rand() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'seller'
        ]);

        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Toko Ujian',
            'close_time' => '23:59:00'
        ]);

        $buyer = $this->createBuyer();
        $order = $this->createOrder($buyer->id, $seller->id, 'diproses');

        // Freeze time to make sure our calculation test is exact
        Carbon::setTestNow(Carbon::createFromTime(18, 0, 0));

        $response = $this->actingAs($sellerUser)->patch(route('seller.orders.ready', $order->id));

        $response->assertRedirect();
        
        $order->refresh();
        $this->assertEquals('siap_diambil', $order->status);
        
        // Assert pickup deadline is exactly 2 hours from now
        $this->assertEquals(Carbon::createFromTime(20, 0, 0)->toDateTimeString(), $order->pickup_deadline->toDateTimeString());
        
        Carbon::setTestNow(); // reset
    }

    public function test_deadline_calculation_respects_store_closing_time()
    {
        $sellerUser = User::create([
            'name' => 'Test Seller',
            'email' => 'seller' . rand() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'seller'
        ]);

        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Toko Ujian',
            'close_time' => '21:00:00'
        ]);

        $buyer = $this->createBuyer();
        $order = $this->createOrder($buyer->id, $seller->id, 'diproses');

        // Set current time to 8 PM
        Carbon::setTestNow(Carbon::createFromTime(20, 0, 0));

        $response = $this->actingAs($sellerUser)->patch(route('seller.orders.ready', $order->id));

        $response->assertRedirect();
        
        $order->refresh();
        $this->assertEquals('siap_diambil', $order->status);
        
        // Assert pickup deadline is capped at 9 PM (not 10 PM)
        $this->assertEquals(Carbon::createFromTime(21, 0, 0)->toDateTimeString(), $order->pickup_deadline->toDateTimeString());
        
        Carbon::setTestNow(); // reset
    }

    public function test_expired_deadline_warning_is_shown()
    {
        $buyer = $this->createBuyer();
        $seller = $this->createSeller();
        
        // Set deadline 1 hour in the past
        $deadline = Carbon::now()->subHour();
        $order = $this->createOrder($buyer->id, $seller->id, 'siap_diambil', $deadline);

        $response = $this->actingAs($buyer)->get(route('buyer.checkout.success', $order->id));

        $response->assertStatus(200);
        $response->assertSee('Batas waktu pengambilan telah terlewat');
    }
}
