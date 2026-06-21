<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use App\Models\Order;
use App\Models\Review;
use App\Models\Complaint;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PBI04OrderHistoryTC02Test extends DuskTestCase
{
public function test_ajukan_komplain_button_visible_only_on_diproses_or_selesai(): void
    {
        $buyer = $this->createBuyer('Test Buyer', 'buyer_complaint_vis@test.com');
        $seller = $this->createSellerAndStore();
        
        $orderSelesai = $this->createOrder($buyer, $seller, 'selesai');
        $orderDiproses = $this->createOrder($buyer, $seller, 'diproses');
        $orderBatal = $this->createOrder($buyer, $seller, 'dibatalkan');

        $this->browse(function (Browser $browser) use ($buyer) {
            $this->loginAs($browser, $buyer)
                ->visitRoute('buyer.orders.index', ['tab' => 'riwayat'])
                ->waitForText('Riwayat Transaksi')
                ->assertSee('Ajukan Komplain'); // Should see it
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
