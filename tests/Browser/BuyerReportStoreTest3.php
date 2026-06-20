<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class BuyerReportStoreTest3 extends DuskTestCase
{
    /**
     * TC-BUY-03: Mengisi report tanpa mengirim foto bukti
     */
    public function test_buyer_submit_report_without_photo(): void
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
                    ->select('kategori', 'Toko Fiktif')
                    ->type('deskripsi', 'Alamat toko ini setelah saya datangi ternyata adalah tanah kosong, tidak ada toko atau penjual sama sekali.') 
                    ->press('Kirim Laporan') 
                    ->pause(3000) 
                    ->screenshot('buyer_report_no_photo_result')
                    ->assertSee('Terima kasih, laporan Anda telah diterima');
        });
    }
}
