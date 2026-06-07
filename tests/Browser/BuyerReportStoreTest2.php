<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class BuyerReportStoreTest2 extends DuskTestCase
{
    /**
     * TC-BUY-02: Melakukan report tanpa mengisi deskripsi (Negative Test)
     */
    public function test_buyer_cannot_report_without_description(): void
    {
        $this->browse(function (Browser $browser) {
            $buyer = User::where('role', 'buyer')->first();
            $seller = Seller::where('verification_status', 'approved')->first();
            \Illuminate\Support\Facades\DB::table('reports')->where('buyer_id', $buyer->id)->where('seller_id', $seller->id)->delete();
            $browser->loginAs($buyer)
                    ->visit('/buyer/store/' . $seller->id)
                    ->press('Laporkan Toko Ini')
                    ->waitForText('Laporkan Toko') 
                    ->select('kategori', 'Penipuan')
                    ->attach('foto_bukti', __DIR__.'/photos/dummy-bukti.png') 
                    ->type('deskripsi', '')
                    ->press('Kirim Laporan') 
                    ->pause(2000) 
                    ->screenshot('buyer_report_no_desc_result')
                    ->assertDontSee('Terima kasih, laporan Anda telah diterima')
                    ->assertSee('Deskripsi Kejadian');
        });
    }
}
