<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function ($table) {
            $table->index('ruc', 'idx_stores_ruc');
            $table->index('slug', 'idx_stores_slug');
            $table->index(['status', 'owner_id'], 'idx_stores_status_owner');
        });

        Schema::table('products', function ($table) {
            $table->index(['store_id', 'status'], 'idx_products_store_status');
            $table->index('price', 'idx_products_price');
            $table->index('rating_promedio', 'idx_products_rating');
            $table->index('stock', 'idx_products_stock');
        });

        Schema::table('orders', function ($table) {
            $table->index(['user_id', 'status'], 'idx_orders_user_status');
            $table->index('created_at', 'idx_orders_created');
            $table->index('payment_status', 'idx_orders_payment');
        });

        Schema::table('shipments', function ($table) {
            $table->index('order_id', 'idx_shipments_order');
            $table->index(['store_id', 'status'], 'idx_shipments_store_status');
            $table->index('tracking_number', 'idx_shipments_tracking');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function ($table) {
            $table->dropIndex('idx_stores_ruc');
            $table->dropIndex('idx_stores_slug');
            $table->dropIndex('idx_stores_status_owner');
        });

        Schema::table('products', function ($table) {
            $table->dropIndex('idx_products_store_status');
            $table->dropIndex('idx_products_price');
            $table->dropIndex('idx_products_rating');
            $table->dropIndex('idx_products_stock');
        });

        Schema::table('orders', function ($table) {
            $table->dropIndex('idx_orders_user_status');
            $table->dropIndex('idx_orders_created');
            $table->dropIndex('idx_orders_payment');
        });

        Schema::table('shipments', function ($table) {
            $table->dropIndex('idx_shipments_order');
            $table->dropIndex('idx_shipments_store_status');
            $table->dropIndex('idx_shipments_tracking');
        });
    }
};
