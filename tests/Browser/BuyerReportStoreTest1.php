<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class BuyerReportStoreTest1 extends DuskTestCase
{
    public function test_buyer_can_submit_full_report(): void
    {
        $this->browse(function (Browser $browser) {
            $buyer = User::where('role', 'buyer')->first();
            $seller = Seller::where('verification_status', 'approved')->first();
            $sellerUser = $seller->user;
            $sellerUser->is_banned = false;
            $sellerUser->save();
            \Illuminate\Support\Facades\DB::table('reports')->where('buyer_id', $buyer->id)->where('seller_id', $seller->id)->delete();
            $browser->loginAs($buyer)
                    ->visit('/buyer/store/' . $seller->id)
                    ->click('#btn-laporkan-toko')
                    ->waitForText('Laporkan Toko') 
                    ->select('kategori', 'Kualitas Makanan Buruk')
                    ->type('deskripsi', 'Makanan yang saya terima dari toko ini ternyata sudah basi dan berbau.') 
                    ->attach('foto_bukti', __DIR__.'/photos/dummy-bukti.png') 
                    ->press('Kirim Laporan') 
                    ->pause(3000) 
                    ->screenshot('buyer_report_result')
                    ->assertSee('Terima kasih, laporan Anda telah diterima'); 
        });
    }
}
