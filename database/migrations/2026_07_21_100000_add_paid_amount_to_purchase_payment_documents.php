<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `amount` is the part applied to the purchase, `paid_amount` is what the
     * supplier actually received — they differ when an overpayment is credited
     * to the supplier balance. Null on legacy rows means "same as amount".
     */
    public function up(): void
    {
        Schema::table('purchase_payment_documents', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_payment_documents', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });
    }
};
