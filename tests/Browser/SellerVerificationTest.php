<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Seller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * PBI-26: Manajemen Pesanan Masuk (Seller)
 */
class SellerVerificationTest extends DuskTestCase
{
    use DatabaseTruncation;

    private User $buyer;
    private User $sellerUserA;
    private Seller $sellerA;
    private User $sellerUserB;
    private Seller $sellerB;

    protected function setUp(): void
    {
        parent::setUp();
        
        $category = Category::first();
        if (!$category) {
            $category = Category::create(['name' => 'Makanan Test']);
        }

        $this->buyer = User::factory()->create([
            'name' => 'Buyer Order',
            'email' => 'buyer_ord_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'buyer',
        ]);

        $this->sellerUserA = User::factory()->create([
            'name' => 'Seller A',
            'email' => 'seller_ord_a_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
        ]);
        $this->sellerA = Seller::create([
            'user_id' => $this->sellerUserA->id,
            'store_name' => 'Toko A',
            'address' => 'Jl. A',
            'verification_status' => 'approved',
        ]);

        $this->sellerUserB = User::factory()->create([
            'name' => 'Seller B',
            'email' => 'seller_ord_b_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
        ]);
        $this->sellerB = Seller::create([
            'user_id' => $this->sellerUserB->id,
            'store_name' => 'Toko B',
            'address' => 'Jl. B',
            'verification_status' => 'approved',
        ]);
    }

    private function createOrderForSeller(Seller $seller, $status, $paymentMethod, $productName)
    {
        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => Category::first()->id,
            'name' => $productName,
            'base_price' => 20000,
            'image' => 'dummy.jpg',
        ]);

        $order = Order::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $seller->id,
            'total_amount' => 40000,
            'status' => $status,
            'payment_method' => $paymentMethod,
            'payment_proof' => $paymentMethod === 'qris' ? 'bukti.jpg' : null,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'seller_id' => $seller->id,
            'qty' => 2,
            'price' => 20000,
            'is_surplus' => false,
        ]);

        return $order;
    }

    /**
     * Memastikan penjual hanya dapat melihat pesanan yang masuk ke tokonya sendiri 
     * (isolasi data antar toko/tenant).
     */
    #[Test]
    #[Group('PBI-26')]
    #[Group('TC-ORD-001')]
    public function test_isolasi_data_pesanan_antar_toko(): void
    {
        $this->createOrderForSeller($this->sellerA, 'menunggu_verifikasi', 'cash', 'Pesanan Toko A');
        $this->createOrderForSeller($this->sellerB, 'menunggu_verifikasi', 'cash', 'Pesanan Toko B');

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->sellerUserA)
                ->visitRoute('seller.orders')
                ->pause(1000)
                ->assertSee('Pesanan Toko A')
                ->assertDontSee('Pesanan Toko B');
        });
    }

    /**
     * Menguji UI berbasis tab pada halaman manajemen pesanan penjual, 
     * memastikan pesanan difilter dan ditampilkan pada tab status yang sesuai.
     */
    #[Test]
    #[Group('PBI-26')]
    #[Group('TC-ORD-002')]
    public function test_render_ui_berbasis_tabs_status(): void
    {
        $this->createOrderForSeller($this->sellerA, 'menunggu_verifikasi', 'cash', 'Menu Baru');
        $this->createOrderForSeller($this->sellerA, 'diproses', 'cash', 'Menu Proses');
        $this->createOrderForSeller($this->sellerA, 'siap_diambil', 'cash', 'Menu Siap');

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->sellerUserA)
                ->visitRoute('seller.orders')
                ->pause(1000)
                ->click('#tab-baru') // Asumsi teks tab
                ->pause(500)
                ->assertSee('Menu Baru')
                
                ->click('#tab-diproses')
                ->pause(500)
                ->assertSee('Menu Proses')
                
                ->click('#tab-siap')
                ->pause(500)
                ->assertSee('Menu Siap');
        });
    }

    /**
     * Menguji bahwa tombol dan fungsionalitas 'Lihat Bukti Transfer' untuk pesanan 
     * QRIS menampilkan modal berisikan gambar bukti pembayaran.
     */
    #[Test]
    #[Group('PBI-26')]
    #[Group('TC-ORD-003')]
    public function test_tombol_lihat_bukti_transfer_qris(): void
    {
        $this->createOrderForSeller($this->sellerA, 'menunggu_verifikasi', 'qris', 'Pesanan QRIS');

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->sellerUserA)
                ->visitRoute('seller.orders')
                ->pause(1000)
                ->assertSee('Lihat Bukti Transfer')
                ->click('.btn-lihat-bukti') // Asumsi class tombol
                ->pause(1000)
                ->assertVisible('#proof-modal'); // Modal muncul
        });
    }

    /**
     * Memastikan penjual dapat menerima pesanan dengan metode QRIS 
     * dan status pesanan berubah menjadi diproses.
     */
    #[Test]
    #[Group('PBI-26')]
    #[Group('TC-ORD-004')]
    public function test_skenario_terima_pembayaran_qris(): void
    {
        $this->createOrderForSeller($this->sellerA, 'menunggu_verifikasi', 'qris', 'Pesanan QRIS Terima');

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->sellerUserA)
                ->visitRoute('seller.orders')
                ->pause(1000)
                ->click('.btn-terima') // Tombol terima
                ->pause(2000)
                ->assertSee('Pembayaran diterima');
        });
    }

    /**
     * Menguji penolakan pesanan QRIS tanpa alasan pembatalan 
     * yang seharusnya memunculkan pesan error / required.
     */
    #[Test]
    #[Group('PBI-26')]
    #[Group('TC-ORD-005')]
    public function test_tolak_pembayaran_qris_negative_case(): void
    {
        $this->createOrderForSeller($this->sellerA, 'menunggu_verifikasi', 'qris', 'Pesanan QRIS Batal');

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->sellerUserA)
                ->visitRoute('seller.orders')
                ->pause(1000)
                ->click('.btn-tolak') // Asumsi tombol tolak
                ->pause(500)
                // Biarkan alasan kosong, klik submit
                ->script("document.getElementById('cancellation_reason').removeAttribute('required');");
            $browser->click('.btn-submit-tolak')
                ->pause(2000)
                ->assertSee('Alasan penolakan wajib diisi');
        });
    }

    /**
     * Menguji skenario penolakan pembayaran QRIS dengan alasan yang valid 
     * dan memastikannya pesanan dibatalkan (hilang dari daftar pesanan aktif).
     */
    #[Test]
    #[Group('PBI-26')]
    #[Group('TC-ORD-006')]
    public function test_tolak_pembayaran_qris_positive_case(): void
    {
        $this->createOrderForSeller($this->sellerA, 'menunggu_verifikasi', 'qris', 'Pesanan QRIS Batal Sukses');

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->sellerUserA)
                ->visitRoute('seller.orders')
                ->pause(1000)
                ->click('.btn-tolak')
                ->pause(500)
                ->select('cancellation_reason', 'Toko tutup')
                ->click('.btn-submit-tolak')
                ->pause(2000)
                ->assertDontSee('Pesanan QRIS Batal Sukses'); // Hilang dari tab baru
        });
    }

    /**
     * Menguji skenario ketika penjual menekan tombol 'Makanan Siap', 
     * maka status akan berpindah dari 'Diproses' ke 'Siap Diambil'.
     */
    #[Test]
    #[Group('PBI-26')]
    #[Group('TC-ORD-007')]
    public function test_skenario_makanan_siap(): void
    {
        $this->createOrderForSeller($this->sellerA, 'diproses', 'cash', 'Pesanan Diproses');

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->sellerUserA)
                ->visitRoute('seller.orders')
                ->pause(1000)
                ->click('#tab-diproses')
                ->waitFor('.btn-siap', 5)
                ->pause(500)
                ->click('.btn-siap')
                ->pause(2000)
                ->assertSee('siap diambil oleh pembeli');
        });
    }

    /**
     * Memastikan pesanan dengan metode Cash tidak memunculkan opsi lihat bukti transfer 
     * dan langsung dapat diproses/diterima oleh penjual.
     */
    #[Test]
    #[Group('PBI-26')]
    #[Group('TC-ORD-008')]
    public function test_visibilitas_tombol_pesanan_cash(): void
    {
        $this->createOrderForSeller($this->sellerA, 'menunggu_verifikasi', 'cash', 'Pesanan Cash Baru');

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->sellerUserA)
                ->visitRoute('seller.orders')
                ->pause(2000)
                ->assertSee('Pesanan Cash Baru')
                ->assertDontSee('Lihat Bukti Transfer') // Tombol ini tidak boleh ada untuk Cash
                ->assertSee('Terima'); // Langsung tombol Terima
        });
    }
}