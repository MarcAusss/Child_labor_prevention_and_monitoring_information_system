<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_information', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('child_laborer_id')
                ->constrained('child_laborers')
                ->cascadeOnDelete();

            $table->date('assessment_date');

            $table->text('health_condition')
                ->nullable();

            $table->boolean('has_disability')
                ->default(false);

            $table->text('disability_details')
                ->nullable();

            $table->text('injury_history')
                ->nullable();

            $table->text('treatment_received')
                ->nullable();

            $table->string('health_facility', 255)
                ->nullable();

            $table->text('current_complaints')
                ->nullable();

            $table->text('mental_health_concerns')
                ->nullable();

            $table->text('remarks')
                ->nullable();

            $table->boolean('is_current')
                ->default(false);

            $table->string('duplicate_key', 64);

            $table->timestamps();

            $table->unique(
                [
                    'child_laborer_id',
                    'duplicate_key',
                ],
                'health_child_duplicate_unique'
            );

            $table->index(
                [
                    'child_laborer_id',
                    'is_current',
                ],
                'health_child_current_idx'
            );

            $table->index(
                [
                    'child_laborer_id',
                    'assessment_date',
                ],
                'health_child_assessment_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_information');
    }
};