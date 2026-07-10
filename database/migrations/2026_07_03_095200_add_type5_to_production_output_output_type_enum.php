<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE production_output MODIFY output_type enum('type1','type2','type3','mixed_family','type4','type5') DEFAULT 'type1'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE production_output MODIFY output_type enum('type1','type2','type3','mixed_family','type4') DEFAULT 'type1'");
    }
};
