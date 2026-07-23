<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_hazards', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('employment_record_id')
                ->constrained('employment_records')
                ->cascadeOnDelete();

            $table->string('hazard_type', 100);

            $table->text('hazard_description');

            $table->string('exposure_frequency', 100);

            $table->text('equipment_machinery')
                ->nullable();

            $table->text('chemicals_substances')
                ->nullable();

            $table->boolean('heavy_work')
                ->default(false);

            $table->boolean('long_hours')
                ->default(false);

            $table->boolean('night_work')
                ->default(false);

            $table->boolean('unsafe_conditions')
                ->default(false);

            $table->boolean('ppe_provided')
                ->default(false);

            $table->text('ppe_description')
                ->nullable();

            $table->text('injuries_incidents')
                ->nullable();

            $table->string('duplicate_key', 64);

            $table->timestamps();

            $table->unique(
                [
                    'employment_record_id',
                    'duplicate_key',
                ],
                'hazard_employment_duplicate_unique'
            );

            $table->index(
                [
                    'employment_record_id',
                    'hazard_type',
                ],
                'hazard_employment_type_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_hazards');
    }
};