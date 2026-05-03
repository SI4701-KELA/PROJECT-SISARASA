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
            $table->renameColumn('status_verified', 'verification_status');
            $table->string('document_path')->nullable()->after('store_photo');
            $table->timestamp('verified_at')->nullable()->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->renameColumn('verification_status', 'status_verified');
            $table->dropColumn(['document_path', 'verified_at']);
        });
    }
};
