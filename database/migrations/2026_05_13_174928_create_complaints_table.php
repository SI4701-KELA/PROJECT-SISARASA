<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->enum('kategori_masalah', [
                'Pesanan Tidak Sesuai',
                'Porsi Kurang',
                'Kualitas Buruk/Basi',
                'Lainnya',
            ]);
            $table->text('deskripsi');
            $table->string('foto_bukti')->nullable();
            $table->text('balasan_admin')->nullable();
            if (Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
                $table->string('status_tiket')->default('menunggu_seller');
            } else {
                $table->enum('status_tiket', ['Open', 'Sedang Diproses', 'Selesai'])->default('Open');
            }
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
