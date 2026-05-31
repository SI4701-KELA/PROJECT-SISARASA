<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Performance indexes untuk kolom yang sering di-query.
     * 
     * Menggunakan raw SQL agar kompatibel dengan SQLite dan semua driver DB.
     * IF NOT EXISTS mencegah error saat re-run migration.
     */
    public function up(): void
    {
        // --- messages ---
        DB::statement('CREATE INDEX IF NOT EXISTS idx_messages_receiver_unread ON messages (receiver_id, is_read)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_messages_conversation ON messages (sender_id, receiver_id)');

        // --- orders ---
        DB::statement('CREATE INDEX IF NOT EXISTS idx_orders_seller_status ON orders (seller_id, status)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_orders_buyer_status ON orders (buyer_id, status)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_orders_pickup_code ON orders (pickup_code)');

        // --- order_items ---
        DB::statement('CREATE INDEX IF NOT EXISTS idx_order_items_surplus ON order_items (order_id, is_surplus)');

        // --- complaints ---
        DB::statement('CREATE INDEX IF NOT EXISTS idx_complaints_buyer_status ON complaints (buyer_id, status_tiket)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_complaints_seller_status ON complaints (seller_id, status_tiket)');

        // --- carts ---
        DB::statement('CREATE INDEX IF NOT EXISTS idx_carts_buyer ON carts (buyer_id)');

        // --- favorite_stores ---
        DB::statement('CREATE INDEX IF NOT EXISTS idx_favorite_stores_user ON favorite_stores (user_id)');

        // --- stocks ---
        DB::statement('CREATE INDEX IF NOT EXISTS idx_stocks_product ON stocks (product_id)');

        // --- discounts ---
        DB::statement('CREATE INDEX IF NOT EXISTS idx_discounts_product_active ON discounts (product_id, is_active)');

        // --- sellers ---
        DB::statement('CREATE INDEX IF NOT EXISTS idx_sellers_verification_status ON sellers (verification_status)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_messages_receiver_unread');
        DB::statement('DROP INDEX IF EXISTS idx_messages_conversation');
        DB::statement('DROP INDEX IF EXISTS idx_orders_seller_status');
        DB::statement('DROP INDEX IF EXISTS idx_orders_buyer_status');
        DB::statement('DROP INDEX IF EXISTS idx_orders_pickup_code');
        DB::statement('DROP INDEX IF EXISTS idx_order_items_surplus');
        DB::statement('DROP INDEX IF EXISTS idx_complaints_buyer_status');
        DB::statement('DROP INDEX IF EXISTS idx_complaints_seller_status');
        DB::statement('DROP INDEX IF EXISTS idx_carts_buyer');
        DB::statement('DROP INDEX IF EXISTS idx_favorite_stores_user');
        DB::statement('DROP INDEX IF EXISTS idx_stocks_product');
        DB::statement('DROP INDEX IF EXISTS idx_discounts_product_active');
        DB::statement('DROP INDEX IF EXISTS idx_sellers_verification_status');
    }
};
