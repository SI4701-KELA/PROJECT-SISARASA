<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use App\Models\Order;
use App\Models\Review;
use App\Models\Complaint;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PBI04OrderHistoryTC01Test extends DuskTestCase
{
public function test_beri_ulasan_button_hidden_if_reviewed(): void
    {
        $buyer = $this->createBuyer('Test Buyer', 'buyer_review@test.com');
        $seller = $this->createSellerAndStore();
        
        // Order 1: Selesai but NOT reviewed
        $order1 = $this->createOrder($buyer, $seller, 'selesai');

        // Order 2: Selesai and REVIEWED
        $order2 = $this->createOrder($buyer, $seller, 'selesai');
        Review::create([
            'order_id' => $order2->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'rating' => 5,
            'comment' => 'Bagus!',
        ]);

        $this->browse(function (Browser $browser) use ($buyer) {
            $this->loginAs($browser, $buyer)
                ->visitRoute('buyer.orders.index', ['tab' => 'riwayat'])
                ->waitForText('Riwayat Transaksi')
                ->assertSee('Beri Ulasan') // from order 1
                ->assertSee('Lihat Ulasan Saya'); // from order 2
        });
    }
    private function createBuyer(string $name, string $email): User
    {
        return User::factory()->create([
            'name' => $name,
            'email' => uniqid() . '_' . $email,
            'role' => 'buyer',
            'password' => bcrypt('password'),
        ]);
    }

    private function createSellerAndStore(): Seller
    {
        $sellerUser = User::factory()->create([
            'name' => 'Seller Complaint',
            'email' => 'seller_complaint_'.uniqid().'@seller.com',
            'role' => 'seller',
            'password' => bcrypt('password'),
        ]);

        return Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Toko Complaint',
            'address' => 'Jl. Test No. 1',
            'verification_status' => 'approved',
        ]);
    }

    private function createOrder(User $buyer, Seller $seller, string $status): Order
    {
        return Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'status' => $status,
            'total_amount' => 50000,
            'payment_method' => 'cash',
            'pickup_code' => 'SISA-' . rand(10000, 99999),
            'pickup_deadline' => now()->addHours(2),
        ]);
    }

    private function loginAs(Browser $browser, User $user): Browser
    {
        return $browser
            ->visit('/login')
            ->waitFor('#email')
            ->type('#email', $user->email)
            ->type('#password', 'password')
            ->press('Login')
            ->waitUntilMissing('#email', 10)
            ->assertPathIsNot('/login');
    }
}
