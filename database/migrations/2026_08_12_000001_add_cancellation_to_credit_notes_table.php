<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Qui a annulé un avoir, quand et pourquoi. Pas de clé étrangère sur
     * cancelled_by: credit_notes n'en porte aucune (users.id est un int signé,
     * les colonnes *_by des bigint non signés).
     */
    public function up()
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('credit_notes', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('approved_at');
            }

            if (!Schema::hasColumn('credit_notes', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
            }

            if (!Schema::hasColumn('credit_notes', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancelled_by');
            }
        });
    }

    public function down()
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->dropColumn(['cancelled_at', 'cancelled_by', 'cancellation_reason']);
        });
    }
};
