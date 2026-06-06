<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Schedule::call(function () {
    $sellers = \App\Models\Seller::whereNotNull('discount_time')->get();
    $now = now()->format('H:i:s');

    foreach ($sellers as $seller) {
        $shouldBeDiscount = false;
        
        // Logika sederhana: jika sekarang melebihi discount_time dan sebelum close_time
        if ($seller->close_time) {
            if ($now >= $seller->discount_time && $now <= $seller->close_time) {
                $shouldBeDiscount = true;
            }
        } else {
            if ($now >= $seller->discount_time) {
                $shouldBeDiscount = true;
            }
        }

        if ($shouldBeDiscount) {
            // Aktifkan diskon secara otomatis
            $products = $seller->products()->with(['discount', 'stock'])->get();
            foreach ($products as $product) {
                $discount = $product->discount;
                if ($discount && !$discount->is_active) {
                    \Illuminate\Support\Facades\DB::transaction(function() use ($product, $discount) {
                        $discount->update(['is_active' => true]);
                        
                        // Pindah stok fisik secara otomatis
                        if ($product->stock && $product->stock->qty_reg > 0) {
                            $product->stock->update([
                                'qty_surplus' => $product->stock->qty_surplus + $product->stock->qty_reg,
                                'qty_reg' => 0
                            ]);
                        }
                    });
                }
            }
        } else {
            // Jika masuk ke jam buka sebelum jam diskon, matikan diskon (Kembalikan ke Reguler)
            if ($seller->open_time && $now >= $seller->open_time && $now < $seller->discount_time) {
                 $products = $seller->products()->with(['discount', 'stock'])->get();
                 foreach ($products as $product) {
                     $discount = $product->discount;
                     if ($discount && $discount->is_active) {
                         \Illuminate\Support\Facades\DB::transaction(function() use ($product, $discount) {
                             $discount->update(['is_active' => false]);
                             
                             // Pindah stok surplus kembali ke reguler jika ada
                             if ($product->stock && $product->stock->qty_surplus > 0) {
                                 $product->stock->update([
                                     'qty_reg' => $product->stock->qty_reg + $product->stock->qty_surplus,
                                     'qty_surplus' => 0
                                 ]);
                             }
                         });
                     }
                 }
            }
        }
    }
})->everyMinute();
