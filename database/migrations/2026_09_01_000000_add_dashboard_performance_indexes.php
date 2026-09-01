<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->index(['is_active', 'client_type'], 'dash_clients_active_type_idx');
            $table->index('created_at', 'dash_clients_created_idx');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->index('order_date', 'dash_sales_order_date_idx');
            $table->index('payment_status', 'dash_sales_payment_status_idx');
        });

        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->index(['item_type', 'item_id'], 'dash_sales_items_type_item_idx');
        });

        Schema::table('sales_order_payments', function (Blueprint $table) {
            $table->index(['status', 'payment_method'], 'dash_payments_status_method_idx');
            $table->index(['payment_date', 'payment_method', 'status'], 'dash_payments_date_method_status_idx');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index('expense_date', 'dash_expenses_date_idx');
        });

        Schema::table('production_orders', function (Blueprint $table) {
            $table->index('status', 'dash_production_status_idx');
            $table->index('start_date', 'dash_production_start_idx');
            $table->index('expected_completion_date', 'dash_production_expected_idx');
        });

        Schema::table('production_output', function (Blueprint $table) {
            $table->index('production_date', 'dash_output_date_idx');
            $table->index('output_type', 'dash_output_type_idx');
        });

        Schema::table('production_consumption', function (Blueprint $table) {
            $table->index(['production_order_id', 'material_id'], 'dash_consumption_order_material_idx');
        });

        Schema::table('production_wastes', function (Blueprint $table) {
            $table->index(['production_order_id', 'waste_type', 'is_recovered'], 'dash_waste_order_type_recovered_idx');
        });

        Schema::table('stock_movement_details', function (Blueprint $table) {
            $table->index(['material_id', 'remaining_quantity'], 'dash_stock_material_remaining_idx');
        });

        Schema::table('product_famille_stock', function (Blueprint $table) {
            $table->index(['product_id', 'famille_id'], 'dash_family_stock_product_family_idx');
            $table->index('current_quantity', 'dash_family_stock_current_idx');
        });

        Schema::table('product_famille', function (Blueprint $table) {
            $table->index(['product_id', 'famille_id'], 'dash_product_family_lookup_idx');
        });

        Schema::table('product_stock', function (Blueprint $table) {
            $table->index('product_id', 'dash_product_stock_product_idx');
        });

        Schema::table('raw_material_purchases', function (Blueprint $table) {
            $table->index(['purchase_date', 'payment_status'], 'dash_purchases_date_status_idx');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index('date', 'dash_attendance_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('dash_clients_active_type_idx');
            $table->dropIndex('dash_clients_created_idx');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropIndex('dash_sales_order_date_idx');
            $table->dropIndex('dash_sales_payment_status_idx');
        });

        Schema::table('sales_order_items', fn (Blueprint $table) => $table->dropIndex('dash_sales_items_type_item_idx'));
        Schema::table('sales_order_payments', function (Blueprint $table) {
            $table->dropIndex('dash_payments_status_method_idx');
            $table->dropIndex('dash_payments_date_method_status_idx');
        });
        Schema::table('expenses', fn (Blueprint $table) => $table->dropIndex('dash_expenses_date_idx'));
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropIndex('dash_production_status_idx');
            $table->dropIndex('dash_production_start_idx');
            $table->dropIndex('dash_production_expected_idx');
        });
        Schema::table('production_output', function (Blueprint $table) {
            $table->dropIndex('dash_output_date_idx');
            $table->dropIndex('dash_output_type_idx');
        });
        Schema::table('production_consumption', fn (Blueprint $table) => $table->dropIndex('dash_consumption_order_material_idx'));
        Schema::table('production_wastes', fn (Blueprint $table) => $table->dropIndex('dash_waste_order_type_recovered_idx'));
        Schema::table('stock_movement_details', fn (Blueprint $table) => $table->dropIndex('dash_stock_material_remaining_idx'));
        Schema::table('product_famille_stock', function (Blueprint $table) {
            $table->dropIndex('dash_family_stock_product_family_idx');
            $table->dropIndex('dash_family_stock_current_idx');
        });
        Schema::table('product_famille', fn (Blueprint $table) => $table->dropIndex('dash_product_family_lookup_idx'));
        Schema::table('product_stock', fn (Blueprint $table) => $table->dropIndex('dash_product_stock_product_idx'));
        Schema::table('raw_material_purchases', fn (Blueprint $table) => $table->dropIndex('dash_purchases_date_status_idx'));
        Schema::table('attendances', fn (Blueprint $table) => $table->dropIndex('dash_attendance_date_idx'));
    }
};
