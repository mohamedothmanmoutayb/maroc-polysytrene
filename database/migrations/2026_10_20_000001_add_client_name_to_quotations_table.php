<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('client_name')->nullable()->after('client_id');
        });

        DB::statement('ALTER TABLE quotations MODIFY client_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('UPDATE quotations SET client_id = 0 WHERE client_id IS NULL');
        DB::statement('ALTER TABLE quotations MODIFY client_id BIGINT UNSIGNED NOT NULL');

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('client_name');
        });
    }
};
