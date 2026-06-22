<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PBI-20: Alur Konfirmasi Seller & Mediasi Admin.
 *
 * Migration ini melakukan dua hal utama:
 *  1. Menambahkan kolom-kolom respons Seller yang bersifat nullable
 *     (tidak merusak data yang sudah ada).
 *  2. Memperluas enum status_tiket dengan nilai 'menunggu_seller' dan
 *     mengubah default-nya, sehingga setiap komplain baru pertama kali
 *     menunggu konfirmasi dari Seller sebelum ditangani Admin.
 *
 * DUSK-SAFE: Test yang sudah ada (TC-CMP-005, 006, 007) menyetel
 * status_tiket secara eksplisit di-code, sehingga perubahan default
 * tidak mempengaruhi mereka.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            // Kolom keputusan Seller: 'approved' atau 'rejected'
            $table->string('seller_action')->nullable()->after('foto_bukti');

            // Alasan penolakan dari Seller (wajib jika seller_action = 'rejected')
            $table->text('seller_reason')->nullable()->after('seller_action');

            // Path foto bukti kelayakan produk yang diunggah Seller saat menolak
            $table->string('seller_proof_path')->nullable()->after('seller_reason');

            // Timestamp saat Seller memberikan respons
            $table->timestamp('seller_responded_at')->nullable()->after('seller_proof_path');
        });

        // Ubah enum status_tiket: tambah nilai 'menunggu_seller' dan ubah default-nya.
        // Harus menggunakan DB::statement() karena Blueprint tidak mendukung
        // perubahan enum secara langsung pada semua versi Laravel/MySQL.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::table('complaints', function (Blueprint $table) {
                $table->string('status_tiket')->default('menunggu_seller')->change();
            });
        } else {
            DB::statement("
                ALTER TABLE complaints
                MODIFY COLUMN status_tiket
                ENUM('menunggu_seller', 'Open', 'Sedang Diproses', 'Selesai')
                NOT NULL DEFAULT 'menunggu_seller'
            ");
        }
    }

    public function down(): void
    {
        // Kembalikan enum ke kondisi semula sebelum menghapus kolom
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::table('complaints', function (Blueprint $table) {
                $table->string('status_tiket')->default('Open')->change();
            });
        } else {
            DB::statement("
                ALTER TABLE complaints
                MODIFY COLUMN status_tiket
                ENUM('Open', 'Sedang Diproses', 'Selesai')
                NOT NULL DEFAULT 'Open'
            ");
        }

        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn([
                'seller_action',
                'seller_reason',
                'seller_proof_path',
                'seller_responded_at',
            ]);
        });
    }
};
