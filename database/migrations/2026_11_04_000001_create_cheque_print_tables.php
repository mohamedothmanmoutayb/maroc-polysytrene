<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cheque_banks', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique();
            $table->string('label');
            $table->decimal('width_mm', 6, 2)->default(175);
            $table->decimal('height_mm', 6, 2)->default(80);
            $table->decimal('offset_x', 5, 2)->default(0);
            $table->decimal('offset_y', 5, 2)->default(0);
            $table->json('fields')->nullable();
            $table->string('sample_path')->nullable();
            $table->timestamps();
        });

        Schema::create('printed_cheques', function (Blueprint $table) {
            $table->id();
            $table->string('bank_key', 40);
            $table->string('cheque_number', 30)->nullable();
            $table->string('account_number', 40)->nullable();
            $table->string('beneficiary');
            $table->decimal('amount', 15, 2);
            $table->text('amount_words');
            $table->string('amount_words_locale', 5)->default('fr');
            $table->string('place', 80);
            $table->date('issue_date');
            $table->string('payable_at', 80)->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('printed_at')->nullable();
            $table->unsignedSmallInteger('print_count')->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['status', 'issue_date']);
            $table->index('beneficiary');
        });

        // Banque par défaut (Attijariwafa), à calibrer sur un chèque réel.
        foreach (config('cheque_print.seed_banks', []) as $key => $label) {
            DB::table('cheque_banks')->insert([
                'key' => $key,
                'label' => $label,
                'width_mm' => config('cheque_print.default_size.width_mm'),
                'height_mm' => config('cheque_print.default_size.height_mm'),
                'fields' => json_encode(config('cheque_print.default_fields')),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('printed_cheques');
        Schema::dropIfExists('cheque_banks');
    }
};
