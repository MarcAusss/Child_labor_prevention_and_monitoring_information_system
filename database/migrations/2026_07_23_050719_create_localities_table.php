<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('localities', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('region_id')
                ->constrained('regions')
                ->restrictOnDelete();

            $table->foreignId('province_id')
                ->nullable()
                ->constrained('provinces')
                ->restrictOnDelete();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('localities')
                ->nullOnDelete();

            $table->string('psgc_code', 10)->unique();
            $table->string('correspondence_code', 9)->nullable();

            $table->string('name', 200);

            $table->string('geographic_level', 30);

            $table->text('old_names')->nullable();
            $table->string('city_class', 30)->nullable();
            $table->string('income_classification', 100)->nullable();
            $table->string('status', 30)->nullable();

            $table->unsignedBigInteger('population')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index([
                'region_id',
                'province_id',
                'is_active',
            ]);

            $table->index([
                'parent_id',
                'is_active',
            ]);

            $table->index([
                'geographic_level',
                'is_active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('localities');
    }
};