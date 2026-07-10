<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('production_orders', function (Blueprint $table) {
            // Quality tracking fields
            $table->enum('quality_status', ['pending', 'good', 'warning', 'critical', 'reviewed'])->default('pending')->after('status');
            $table->decimal('quality_score', 5, 2)->nullable()->after('quality_status');
            $table->decimal('raw_material_weight_kg', 12, 2)->nullable()->after('quality_score');
            $table->decimal('product_weight_kg', 12, 2)->nullable()->after('raw_material_weight_kg');
            $table->decimal('weight_difference_percent', 8, 2)->nullable()->after('product_weight_kg');
            $table->text('quality_notes')->nullable()->after('weight_difference_percent');
            $table->timestamp('quality_checked_at')->nullable()->after('quality_notes');
            $table->unsignedBigInteger('quality_checked_by')->nullable()->after('quality_checked_at');

            // Quality override (allow completion even if quality check fails)
            $table->boolean('quality_override')->default(false)->after('quality_checked_by');
            $table->text('quality_override_reason')->nullable()->after('quality_override');
            $table->timestamp('quality_override_at')->nullable()->after('quality_override_reason');
            $table->unsignedBigInteger('quality_override_by')->nullable()->after('quality_override_at');

            // Production quality metrics
            $table->decimal('defect_rate_percent', 8, 2)->nullable()->after('quality_override_by');
            $table->integer('total_good_quantity')->default(0)->after('defect_rate_percent');
            $table->integer('total_defective_quantity')->default(0)->after('total_good_quantity');
            $table->decimal('efficiency_percent', 8, 2)->nullable()->after('total_defective_quantity');
        });
    }

    public function down()
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropColumn([
                'quality_status',
                'quality_score',
                'raw_material_weight_kg',
                'product_weight_kg',
                'weight_difference_percent',
                'quality_notes',
                'quality_checked_at',
                'quality_checked_by',
                'quality_override',
                'quality_override_reason',
                'quality_override_at',
                'quality_override_by',
                'defect_rate_percent',
                'total_good_quantity',
                'total_defective_quantity',
                'efficiency_percent'
            ]);
        });
    }
};
