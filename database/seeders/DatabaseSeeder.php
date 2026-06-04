<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Admin
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate([
            'email' => 'admin123@gmail.com',
        ], [
            'name' => 'Admin User',
            'role' => 'admin',
            'password' => bcrypt('1234567#'),
        ]);

        User::updateOrCreate([
            'email' => 'fazrilfazril92@gmail.com',
        ], [
            'name' => 'Fazril Admin',
            'role' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        User::updateOrCreate([
            'email' => 'fazril1805@gmail.com',
        ], [
            'name' => 'Fazril Buyer',
            'role' => 'buyer',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        User::updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => bcrypt('password123'),
        ]);

        // 2. Kategori Default
        $categories = [
            'Makanan Berat',
            'Cemilan & Pastry',
            'Minuman',
            'Sayuran & Buah'
        ];

        foreach ($categories as $category) {
            \App\Models\Category::firstOrCreate(['name' => $category]);
        }

        // 3. Dummy Sellers
        $this->call(DummySellerSeeder::class);

        // Ambil ID Seller Warung Nasi Budi untuk menautkan voucher
        $seller = \App\Models\Seller::where('store_name', 'Warung Nasi Budi')->first();
        $sellerId = $seller ? $seller->id : 1;

        // 4. Seeding Vouchers
        \App\Models\Voucher::firstOrCreate(
            ['code' => 'SISARASABARU'],
            [
                'seller_id' => $sellerId,
                'type' => 'percent',
                'value' => 10, // 10%
                'min_order' => 10000,
                'is_active' => true,
            ]
        );

        \App\Models\Voucher::firstOrCreate(
            ['code' => 'SISARASA10K'],
            [
                'seller_id' => $sellerId,
                'type' => 'fixed',
                'value' => 10000, // Rp 10.000
                'min_order' => 20000,
                'is_active' => true,
            ]
        );
    }
}
