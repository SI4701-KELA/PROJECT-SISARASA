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
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn('status_verified');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->string('status_verified')->default('pending');
            $table->text('rejection_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn(['status_verified', 'rejection_reason']);
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->boolean('status_verified')->default(false);
        });
    }
};
