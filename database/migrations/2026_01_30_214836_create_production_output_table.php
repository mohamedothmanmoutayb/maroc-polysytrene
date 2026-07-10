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
        Schema::table('production_output', function (Blueprint $table) {
            // Add source_famille_id column
            if (!Schema::hasColumn('production_output', 'source_famille_id')) {
                $table->unsignedBigInteger('source_famille_id')->nullable()->after('famille_name');
            }

            // Add output_type column
            if (!Schema::hasColumn('production_output', 'output_type')) {
                $table->enum('output_type', ['type1', 'type2', 'type3'])->default('type1')->after('source_famille_id');
            }

            // Add quantity_consumed column
            if (!Schema::hasColumn('production_output', 'quantity_consumed')) {
                $table->integer('quantity_consumed')->default(0)->after('quantity_produced');
            }

            // Add approved_at column
            if (!Schema::hasColumn('production_output', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }

            // Add is_final_output column
            if (!Schema::hasColumn('production_output', 'is_final_output')) {
                $table->boolean('is_final_output')->default(false)->after('approved_at');
            }

            // Add related_output_id column
            if (!Schema::hasColumn('production_output', 'related_output_id')) {
                $table->unsignedBigInteger('related_output_id')->nullable()->after('is_final_output');
            }

            // Add conversion_data column if not exists
            if (!Schema::hasColumn('production_output', 'conversion_data')) {
                $table->json('conversion_data')->nullable()->after('apply_conversion');
            }

            // Add foreign key constraints
            if (Schema::hasTable('familles')) {
                $table->foreign('source_famille_id')->references('famille_id')->on('familles')->onDelete('set null');
            }

            if (Schema::hasTable('production_output')) {
                $table->foreign('related_output_id')->references('output_id')->on('production_output')->onDelete('set null');
            }

            // Add indexes for better performance
            $table->index('output_type');
            $table->index('is_final_output');
            $table->index('related_output_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_output', function (Blueprint $table) {
            // Remove foreign keys
            $table->dropForeign(['source_famille_id']);
            $table->dropForeign(['related_output_id']);

            // Remove indexes
            $table->dropIndex(['output_type']);
            $table->dropIndex(['is_final_output']);
            $table->dropIndex(['related_output_id']);

            // Remove columns
            $table->dropColumn('source_famille_id');
            $table->dropColumn('output_type');
            $table->dropColumn('quantity_consumed');
            $table->dropColumn('approved_at');
            $table->dropColumn('is_final_output');
            $table->dropColumn('related_output_id');

            // Note: Don't drop conversion_data if it already existed
            if (Schema::hasColumn('production_output', 'conversion_data')) {
                $table->dropColumn('conversion_data');
            }
        });
    }
};
