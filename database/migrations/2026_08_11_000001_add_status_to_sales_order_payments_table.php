<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A règlement paid by chèque or traite can come back unpaid. It used to be deleted
 * on the spot, which left no trace of what the client had handed over; it is now
 * kept and flagged, so the vente shows the règlement as impayé instead of it simply
 * vanishing. Flagged règlements are excluded from every total by a global scope on
 * the model.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_order_payments', function (Blueprint $table) {
            $table->string('status', 20)->default('valid')->after('payment_method');
            $table->timestamp('bounced_at')->nullable()->after('status');
            $table->string('bounce_reason')->nullable()->after('bounced_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('sales_order_payments', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'bounced_at', 'bounce_reason']);
        });
    }
};
