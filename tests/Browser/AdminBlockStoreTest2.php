<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use App\Models\Report;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Facades\DB;

class AdminBlockStoreTest2 extends DuskTestCase
{
    /**
     * TC-ADM-02: Admin memblokir toko berdasarkan laporan yang valid
     */
    public function test_admin_can_ban_store(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = User::firstOrCreate(
                ['email' => 'admin_block@example.com'],
                ['name' => 'Admin Test', 'role' => 'admin', 'password' => bcrypt('password')]
            );
            $buyer = User::firstOrCreate(
                ['email' => 'buyer_block@example.com'],
                ['name' => 'Buyer Test', 'role' => 'buyer', 'password' => bcrypt('password')]
            );
            $sellerUser = User::firstOrCreate(
                ['email' => 'seller_block@example.com'],
                ['name' => 'Seller Test', 'role' => 'seller', 'password' => bcrypt('password')]
            );
            $seller = Seller::firstOrCreate(
                ['user_id' => $sellerUser->id],
                ['store_name' => 'Toko Block', 'address' => 'Jl. Test', 'latitude' => 0, 'longitude' => 0, 'verification_status' => 'approved']
            );
            DB::table('reports')->delete();   
            Report::create([
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'kategori' => 'Penipuan',
                'deskripsi' => 'Laporan penipuan untuk diuji fitur blokir tokonya oleh Dusk.',
                'status' => 'Pending'
            ]);

            $sellerUser = $seller->user;
            $sellerUser->is_banned = false;
            $sellerUser->save();
            $browser->loginAs($admin)
                    ->visit('/admin/reports')
                    ->waitForText('Daftar Laporan Pembeli')
                    ->pause(1000)
                    ->press('Tindak Lanjuti')
                    ->waitForText('Pilih tindakan untuk toko')
                    ->pause(1000)
                    ->press('Blokir Toko')
                    ->acceptDialog() 
                    ->pause(2000)
                    ->screenshot('admin_ban_store_result')         
                    ->assertPathIs('/admin/reports');

            $this->assertDatabaseHas('reports', [
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'status' => 'Selesai'
            ]);

            $this->assertDatabaseHas('users', [
                'id' => $seller->user_id,
                'is_banned' => 1
            ]);
        });
    }
}
