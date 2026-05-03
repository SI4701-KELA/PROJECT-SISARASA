<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Seller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummySellerSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name'       => 'Budi Santoso',
                'email'      => 'budi@seller.com',
                'store_name' => 'Warung Nasi Budi',
                'address'    => 'Jl. Sudirman No. 12, Bandung, Jawa Barat 40111',
                'latitude'   => -6.9147,
                'longitude'  => 107.6098,
            ],
            [
                'name'       => 'Siti Rahayu',
                'email'      => 'siti@seller.com',
                'store_name' => 'Kedai Siti Jaya',
                'address'    => 'Jl. Gatot Subroto No. 5, Jakarta Selatan 12930',
                'latitude'   => -6.2341,
                'longitude'  => 106.7993,
            ],
            [
                'name'       => 'Ahmad Fauzi',
                'email'      => 'ahmad@seller.com',
                'store_name' => 'Toko Roti Ahmad Premium',
                'address'    => 'Jl. Diponegoro No. 88, Surabaya, Jawa Timur 60271',
                'latitude'   => -7.2575,
                'longitude'  => 112.7521,
            ],
        ];

        foreach ($data as $d) {
            $user = User::firstOrCreate(
                ['email' => $d['email']],
                [
                    'name'              => $d['name'],
                    'password'          => Hash::make('password123'),
                    'role'              => 'seller',
                    'email_verified_at' => now(),
                ]
            );

            Seller::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'store_name'          => $d['store_name'],
                    'address'             => $d['address'],
                    'latitude'            => $d['latitude'],
                    'longitude'           => $d['longitude'],
                    'open_time'           => '08:00:00',
                    'close_time'          => '20:00:00',
                    'discount_time'       => '18:00:00',
                    'verification_status' => 'pending',
                ]
            );

            $this->command->info("✓ Created seller: {$d['store_name']}");
        }
    }
}
