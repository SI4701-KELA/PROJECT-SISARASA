<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            // order_id dibuat UNIQUE agar 1 resi/pesanan hanya bisa dinilai 1 kali saja (anti-spam)
            $table->foreignId('order_id')->unique()->constrained('orders')->onDelete('cascade');
            // buyer_id merujuk ke tabel users pembeli
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            // seller_id merujuk ke tabel sellers (toko/UMKM)
            $table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
            // rating bintang (1 sampai 5)
            $table->tinyInteger('rating');
            // komentar ulasan tertulis dari pembeli (nullable/opsional)
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
