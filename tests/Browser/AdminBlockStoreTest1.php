<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use App\Models\Report;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Facades\DB;

class AdminBlockStoreTest1 extends DuskTestCase
{
    public function test_admin_can_reject_report(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = User::where('role', 'admin')->first();
            $buyer = User::where('role', 'buyer')->first();
            $seller = Seller::first();   
            DB::table('reports')->delete();   
            Report::create([
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'kategori' => 'Lainnya',
                'deskripsi' => 'Laporan uji coba Dusk ini sengaja dibuat agar bisa ditolak oleh admin.',
                'status' => 'Pending'
            ]);

            $browser->loginAs($admin)
                    ->visit('/admin/reports')
                    ->waitForText('Daftar Laporan Pembeli')
                    ->press('Tindak Lanjuti')
                    ->waitForText('Pilih tindakan untuk toko')
                    ->pause(1000)
                    ->press('Tolak Laporan')
                    ->pause(2000)
                    ->screenshot('admin_reject_report_result')
                    ->assertPathIs('/admin/reports');
            $this->assertDatabaseHas('reports', [
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'status' => 'Ditolak'
            ]);
        });
    }
}
