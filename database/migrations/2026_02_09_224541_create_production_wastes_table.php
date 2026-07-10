<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProductionWastesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('production_wastes', function (Blueprint $table) {
            $table->id('waste_id');

            // Foreign keys
            $table->foreignId('production_order_id')
                  ->constrained('production_orders', 'order_id')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreignId('material_id')
                  ->nullable()
                  ->constrained('raw_materials', 'material_id')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            // Waste information
            $table->enum('waste_type', ['recyclable', 'waste', 'auto_defective'])
                  ->default('recyclable');

            $table->string('waste_source', 100)
                  ->default('Découpage')
                  ->comment('Source de la chute: Découpage, Production, Finishing, etc.');

            $table->string('waste_category', 100)
                  ->nullable()
                  ->comment('Catégorie de déchet pour les déchets non recyclables');

            // Dimensions (for volumetric waste)
            $table->decimal('height', 10, 4)
                  ->nullable()
                  ->comment('Hauteur en mètres');

            $table->decimal('width', 10, 4)
                  ->nullable()
                  ->comment('Largeur en mètres');

            $table->decimal('depth', 10, 4)
                  ->nullable()
                  ->comment('Profondeur en mètres');

            $table->decimal('quantity', 10, 4)
                  ->default(1)
                  ->comment('Quantité de pièces');

            $table->decimal('volume_m3', 12, 6)
                  ->default(0)
                  ->comment('Volume total en mètres cubes');

            // Waste management
            $table->boolean('is_recovered')
                  ->default(false)
                  ->comment('Si le déchet a été récupéré/réutilisé');

            $table->dateTime('recovery_date')
                  ->nullable()
                  ->comment('Date de récupération');

            // Additional info
            $table->text('notes')
                  ->nullable()
                  ->comment('Notes sur la chute');

            // Audit trail
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users', 'user_id')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users', 'user_id')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->foreignId('deleted_by')
                  ->nullable()
                  ->constrained('users', 'user_id')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('production_order_id');
            $table->index('waste_type');
            $table->index('waste_source');
            $table->index('is_recovered');
            $table->index('created_by');
            $table->index(['production_order_id', 'waste_type']);

            // Composite index for common queries
            $table->index(['production_order_id', 'is_recovered']);
            $table->index(['waste_type', 'is_recovered']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_wastes');
    }
}
