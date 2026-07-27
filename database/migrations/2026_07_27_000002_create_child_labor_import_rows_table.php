<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('child_labor_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('child_labor_import_batches')->cascadeOnDelete();
            $table->unsignedInteger('sheet_row');
            $table->string('child_id_number')->nullable()->index();
            $table->string('child_name')->nullable();
            $table->string('status')->default('valid');
            $table->json('normalized_data');
            $table->json('warnings')->nullable();
            $table->json('errors')->nullable();
            $table->string('resolution')->nullable();
            $table->unsignedBigInteger('child_laborer_id')->nullable()->index();
            $table->timestamps();
            $table->unique(['batch_id', 'sheet_row']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_labor_import_rows');
    }
};
