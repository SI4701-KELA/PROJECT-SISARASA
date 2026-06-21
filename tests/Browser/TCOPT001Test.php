<?php

namespace Tests\Browser;

use App\Models\Product;
use App\Models\Category;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

/**
 * PBI-31: Tuning Website
 */
class TCOPT001Test extends DuskTestCase
{
    use DatabaseTruncation;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->buyer = User::factory()->create([
            'name' => 'Buyer Test',
            'email' => 'buyer_opt_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'buyer',
        ]);
    }

    /**
     * Memastikan halaman menu/katalog dapat dimuat dalam waktu kurang dari batas toleransi 
     * (di bawah 5 detik) untuk memastikan performa yang baik bagi pengguna.
     */
    #[Test]
    #[Group('PBI-31')]
    #[Group('TC-OPT-001')]
    public function test_penurunan_waktu_muat_pada_katalog(): void
    {
        $this->browse(function (Browser $browser) {
            $start = microtime(true);
            
            $browser->loginAs($this->buyer)
                ->visitRoute('buyer.menu')
                ->pause(500)
                ->assertSee('Menu');
            
            $end = microtime(true);
            $loadTime = $end - $start;

            // Pastikan dimuat kurang dari 3 detik (diperlonggar untuk server lokal testing)
            $this->assertLessThan(5.0, $loadTime, 'Halaman dimuat lebih dari 5 detik, performa perlu dicek.');
        });
    }
}
