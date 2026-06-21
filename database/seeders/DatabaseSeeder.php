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
            'password' => '1234567#',
        ]);

        User::updateOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => bcrypt('password'),
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
    }
}
