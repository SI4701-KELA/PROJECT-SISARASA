<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Kolom `photo` menyimpan path relatif (maks. 255). Hash kata sandi butuh panjang
     * lebih dari 25 karakter; kolom password tetap VARCHAR(255) agar kompatibel bcrypt.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'profile_photo') && ! Schema::hasColumn('users', 'photo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('photo', 255)->nullable()->after('role');
            });

            DB::table('users')->whereNotNull('profile_photo')->update([
                'photo' => DB::raw('profile_photo'),
            ]);

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('profile_photo');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'photo') && ! Schema::hasColumn('users', 'profile_photo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('profile_photo')->nullable()->after('role');
            });

            DB::table('users')->whereNotNull('photo')->update([
                'profile_photo' => DB::raw('photo'),
            ]);

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('photo');
            });
        }
    }
};
