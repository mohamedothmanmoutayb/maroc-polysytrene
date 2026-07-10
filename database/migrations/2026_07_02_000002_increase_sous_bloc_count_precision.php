<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE production_orders MODIFY sous_bloc_count DECIMAL(10,2) NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE production_orders MODIFY sous_bloc_count DECIMAL(10,0) NULL');
    }
};
