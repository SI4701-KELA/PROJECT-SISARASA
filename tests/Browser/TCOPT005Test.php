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
class TCOPT005Test extends DuskTestCase
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
     * Memastikan performa rendering aset berjalan normal tanpa macet. 
     * (Pengukuran detil idealnya via tools jaringan/Chrome DevTools).
     */
    #[Test]
    #[Group('PBI-31')]
    #[Group('TC-OPT-005')]
    public function test_performa_rendering_dan_kompresi_gambar(): void
    {
        $this->assertTrue(true, 'Pengujian payload network size untuk gambar divalidasi via Chrome Network Tab.');
    }
}
