<?php

namespace Tests\Browser;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Models\Complaint;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * PBI-20: Ticketing Komplain (E2E Tests)
 */
class ComplaintDuskTest extends DuskTestCase
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

    private function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin_complaint@admin.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);
    }

    private function createSellerAndStore(): Seller
    {
        $sellerUser = User::factory()->create([
            'name' => 'Seller Complaint',
            'email' => 'seller_complaint@seller.com',
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
        $order = Order::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'status' => $status,
            'total_amount' => 50000,
            'payment_method' => 'cash',
        ]);
        
        $category = \App\Models\Category::firstOrCreate(['name' => 'Makanan Test']);
        
        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Produk Test',
            'base_price' => 50000,
            'image' => 'dummy.jpg',
        ]);
        
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 50000,
        ]);

        return $order;
    }

    private function loginAs(Browser $browser, User $user): Browser
    {
        return $browser->loginAs($user);
    }

    /**
     * Skenario: 
     * Buat 1 pesanan 'Selesai' dan 1 pesanan 'Dibatalkan' untuk Buyer A.
     * Kunjungi detail pesanan masing-masing, pastikan tombol "Ajukan Komplain" ada atau tidak.
     */
    #[Test]
    #[Group('complaint')]
    #[Group('TC-CMP-001')]
    public function test_tombol_komplain_muncul_berdasarkan_status(): void
    {
        $buyerA = $this->createBuyer('Buyer A', 'buyera@complaint.test');
        $seller = $this->createSellerAndStore();
        
        $orderSelesai = $this->createOrder($buyerA, $seller, 'selesai');
        $orderBatal = $this->createOrder($buyerA, $seller, 'dibatalkan');

        $this->browse(function (Browser $browser) use ($buyerA, $orderSelesai, $orderBatal) {
            $this->loginAs($browser, $buyerA)
                // Order Selesai
                ->visitRoute('buyer.orders.show', $orderSelesai->id)
                ->waitForText('NOTA PEMBAYARAN RESMI')
                ->assertSee('Ajukan Komplain')
                
                // Order Dibatalkan
                ->visitRoute('buyer.orders.show', $orderBatal->id)
                ->waitForText('NOTA PEMBAYARAN RESMI')
                ->assertDontSee('Ajukan Komplain');
        });
    }

    /**
     * Skenario: 
     * Buat Order milik Buyer B.
     * Login sebagai Buyer A. Kunjungi halaman komplain secara paksa ke seller tersebut
     * dengan membawa order_id (simulasi IDOR test).
     */
    #[Test]
    #[Group('complaint')]
    #[Group('TC-CMP-002')]
    public function test_pembeli_tidak_bisa_mengakses_komplain_orang_lain(): void
    {
        $buyerA = $this->createBuyer('Buyer A', 'buyera2@complaint.test');
        $buyerB = $this->createBuyer('Buyer B', 'buyerb@complaint.test');
        $seller = $this->createSellerAndStore();
        
        $orderB = $this->createOrder($buyerB, $seller, 'selesai');

        $this->browse(function (Browser $browser) use ($buyerA, $seller, $orderB) {
            $this->loginAs($browser, $buyerA);
            
            $url = route('buyer.complaint.create', ['seller' => $seller->id]) . '?order_id=' . $orderB->id;
            $browser->visit($url);
            
            // Skenario mengharapkan sistem menolak (403/404) atau redirect.
            // Karena tidak ada throw abort() yang menyebabkan halaman error,
            // kita asumsikan sistem seharusnya me-redirect kembali ke halaman sebelumnya.
            $browser->assertPathIsNot('/buyer/stores/' . $seller->id . '/complaint');
        });
    }

    /**
     * Skenario: 
     * Validasi ketika kategori Kualitas Buruk/Basi dipilih, foto bukti wajib diunggah.
     */
    #[Test]
    #[Group('complaint')]
    #[Group('TC-CMP-003')]
    public function test_validasi_foto_bukti_untuk_kategori_basi(): void
    {
        $buyerA = $this->createBuyer('Buyer A', 'buyera3@complaint.test');
        $seller = $this->createSellerAndStore();
        $this->createOrder($buyerA, $seller, 'selesai'); // Tambahkan order Selesai agar bisa komplain

        $this->browse(function (Browser $browser) use ($buyerA, $seller) {
            $this->loginAs($browser, $buyerA)
                ->visitRoute('buyer.complaint.create', $seller->id)
                ->select('kategori_masalah', 'Kualitas Buruk/Basi')
                ->type('deskripsi', 'Kualitas makanan ini sangat buruk dan basi, mohon refund secepatnya karena ini tidak layak makan.')
                // Secara eksplisit tidak melampirkan foto
                // Hapus atribut required HTML5 agar form bisa submit ke server
                // sehingga validasi Laravel server-side yang menampilkan pesan error
                ->script("document.getElementById('foto_bukti').removeAttribute('required')");

            $browser->press('Kirim Komplain')
                ->waitForText('Foto bukti wajib diunggah untuk kategori Kualitas Buruk/Basi', 10);
        });
    }

    /**
     * Skenario: 
     * Sukses mengirim komplain.
     */
    #[Test]
    #[Group('complaint')]
    #[Group('TC-CMP-004')]
    public function test_relasi_database_order_dan_buyer_benar(): void
    {
        $buyerA = $this->createBuyer('Buyer A', 'buyera4@complaint.test');
        $seller = $this->createSellerAndStore();
        $this->createOrder($buyerA, $seller, 'selesai'); // Tambahkan order Selesai agar bisa komplain
        
        $this->browse(function (Browser $browser) use ($buyerA, $seller) {
            $this->loginAs($browser, $buyerA)
                ->visitRoute('buyer.complaint.create', $seller->id)
                ->select('kategori_masalah', 'Porsi Kurang')
                ->type('deskripsi', 'Porsi makanannya jauh berbeda dari foto, mohon segera ditindaklanjuti karena ini mengecewakan.')
                ->press('Kirim Komplain')
                ->waitForLocation('/buyer/complaints')
                ->assertSee('Komplain berhasil');
        });

        // Verifikasi database
        $this->assertDatabaseHas('complaints', [
            'buyer_id' => $buyerA->id,
            'seller_id' => $seller->id,
            'kategori_masalah' => 'Porsi Kurang',
        ]);
    }

    /**
     * Skenario: 
     * Admin membalas komplain dan mengubah status menjadi "Selesai". Buyer kemudian dapat melihatnya.
     */
    #[Test]
    #[Group('complaint')]
    #[Group('TC-CMP-005')]
    #[Group('TC-CMP-006')]
    public function test_admin_bisa_membalas_dan_mengubah_status_tiket(): void
    {
        $buyerA = $this->createBuyer('Buyer A', 'buyera5@complaint.test');
        $admin = $this->createAdmin();
        $seller = $this->createSellerAndStore();
        
        $complaint = Complaint::create([
            'buyer_id' => $buyerA->id,
            'seller_id' => $seller->id,
            'kategori_masalah' => 'Pesanan Tidak Sesuai',
            'deskripsi' => 'Pesanan tidak sesuai dengan apa yang saya pesan sebelumnya.',
            'status_tiket' => 'Open',
        ]);

        $this->browse(function (Browser $browser) use ($admin, $buyerA, $complaint) {
            // Login Admin
            $this->loginAs($browser, $admin)
                ->visitRoute('admin.complaints.index')
                ->waitForText('#' . $complaint->id)
                ->press('Tinjau')
                ->pause(1500) // Tunggu Alpine modal terbuka dengan animasi
                ->waitForText('PEMBELI') // Teks di dalam modal
                ->waitFor('textarea[name="balasan_admin"]', 10) // Pastikan textarea sudah muncul
                ->scrollIntoView('textarea[name="balasan_admin"]')
                ->pause(800); // Tunggu scroll selesai

            // ->script() mengembalikan array, tidak bisa di-chain — break chain di sini
            $browser->script("document.querySelector('textarea[name=\"balasan_admin\"]').focus(); document.querySelector('textarea[name=\"balasan_admin\"]').value = 'Dana akan di-refund sepenuhnya, mohon maaf atas ketidaknyamanan ini.';");

            $browser->select('status_tiket', 'Selesai')
                ->pause(500)
                ->press('Simpan & Perbarui')
                ->pause(2000) // Tunggu halaman selesai dimuat ulang (redirect)
                ->assertPathIs('/admin/complaints')
                ->assertSee('berhasil diperbarui');
        });
    }

    /**
     * Skenario: 
     * Pembeli dapat membaca balasan Admin.
     */
    #[Test]
    #[Group('complaint')]
    #[Group('TC-CMP-006')]
    public function test_pembeli_bisa_membaca_balasan_admin(): void
    {
        $buyerA = $this->createBuyer('Buyer A', 'buyera6@complaint.test');
        $seller = $this->createSellerAndStore();
        
        $complaint = Complaint::create([
            'buyer_id' => $buyerA->id,
            'seller_id' => $seller->id,
            'kategori_masalah' => 'Pesanan Tidak Sesuai',
            'deskripsi' => 'Pesanan tidak sesuai.',
            'balasan_admin' => 'Dana di-refund sepenuhnya.',
            'status_tiket' => 'Selesai',
        ]);

        $this->browse(function (Browser $browser) use ($buyerA, $complaint) {
            $this->loginAs($browser, $buyerA)
                ->visitRoute('buyer.complaints.index')
                ->waitForText('#' . $complaint->id)
                ->assertSee('Dana di-refund sepenuhnya.')
                ->assertSee('Selesai');
        });
    }

    /**
     * Skenario: 
     * Pembatasan edit tiket jika status sudah "Selesai".
     */
    #[Test]
    #[Group('complaint')]
    #[Group('TC-CMP-007')]
    public function test_batasan_edit_tiket_jika_sedang_diproses(): void
    {
        $buyerA = $this->createBuyer('Buyer A', 'buyera7@complaint.test');
        $seller = $this->createSellerAndStore();
        $this->createOrder($buyerA, $seller, 'selesai');
        
        // Buat tiket sudah Selesai
        Complaint::create([
            'buyer_id' => $buyerA->id,
            'seller_id' => $seller->id,
            'kategori_masalah' => 'Porsi Kurang',
            'deskripsi' => 'Porsi kurang.',
            'status_tiket' => 'Selesai',
        ]);

        $this->browse(function (Browser $browser) use ($buyerA, $seller) {
            $this->loginAs($browser, $buyerA)
                // Coba masuk ke form buat komplain lagi ke toko ini
                ->visitRoute('buyer.complaint.create', $seller->id)
                // Seharusnya tidak ada tombol untuk edit form yang lama
                ->assertDontSee('Edit Komplain');
        });
    }
}
