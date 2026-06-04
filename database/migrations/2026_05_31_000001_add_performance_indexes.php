<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Performance indexes untuk kolom yang sering di-query.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['receiver_id', 'is_read'], 'idx_messages_receiver_unread');
            $table->index(['sender_id', 'receiver_id'], 'idx_messages_conversation');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['seller_id', 'status'], 'idx_orders_seller_status');
            $table->index(['buyer_id', 'status'], 'idx_orders_buyer_status');
            $table->index('pickup_code', 'idx_orders_pickup_code');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['order_id', 'is_surplus'], 'idx_order_items_surplus');
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->index(['buyer_id', 'status_tiket'], 'idx_complaints_buyer_status');
            $table->index(['seller_id', 'status_tiket'], 'idx_complaints_seller_status');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->index('buyer_id', 'idx_carts_buyer');
        });

        Schema::table('favorite_stores', function (Blueprint $table) {
            $table->index('user_id', 'idx_favorite_stores_user');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->index('product_id', 'idx_stocks_product');
        });

        Schema::table('discounts', function (Blueprint $table) {
            $table->index(['product_id', 'is_active'], 'idx_discounts_product_active');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->index('verification_status', 'idx_sellers_verification_status');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('idx_messages_receiver_unread');
            $table->dropIndex('idx_messages_conversation');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_seller_status');
            $table->dropIndex('idx_orders_buyer_status');
            $table->dropIndex('idx_orders_pickup_code');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('idx_order_items_surplus');
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->dropIndex('idx_complaints_buyer_status');
            $table->dropIndex('idx_complaints_seller_status');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex('idx_carts_buyer');
        });

        Schema::table('favorite_stores', function (Blueprint $table) {
            $table->dropIndex('idx_favorite_stores_user');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropIndex('idx_stocks_product');
        });

        Schema::table('discounts', function (Blueprint $table) {
            $table->dropIndex('idx_discounts_product_active');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->dropIndex('idx_sellers_verification_status');
        });
    }
};
