<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use App\Models\Order;
use App\Models\Review;
use App\Models\Complaint;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class OrderHistoryButtonDuskTest extends DuskTestCase
{
    use DatabaseTruncation;

    private function createBuyer(string $name, string $email): User
    {
        return User::factory()->create([
            'name' => $name,
            'email' => $email,
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

    public function test_ajukan_komplain_button_changes_to_lihat_status_if_active_complaint_exists(): void
    {
        $buyer = $this->createBuyer('Test Buyer', 'buyer_complaint_active@test.com');
        $seller = $this->createSellerAndStore();
        
        $order = $this->createOrder($buyer, $seller, 'selesai');

        // Create an active complaint
        Complaint::create([
            'seller_id' => $seller->id,
            'buyer_id' => $buyer->id,
            'kategori_masalah' => 'Porsi Kurang',
            'deskripsi' => 'Testing',
            'status_tiket' => 'Open',
        ]);

        $this->browse(function (Browser $browser) use ($buyer) {
            $this->loginAs($browser, $buyer)
                ->visitRoute('buyer.orders.index', ['tab' => 'riwayat'])
                ->waitForText('Riwayat Transaksi')
                ->assertDontSee('Ajukan Komplain')
                ->assertSee('Lihat Status Komplain');
        });
    }
}
