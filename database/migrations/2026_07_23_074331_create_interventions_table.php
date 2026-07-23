<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('child_laborer_id')
                ->constrained('child_laborers')
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('intervention_type', 150);

            $table->string('provider', 255);

            $table->text('description');

            $table->date('date_provided')
                ->nullable();

            $table->date('date_completed')
                ->nullable();

            $table->decimal('amount', 12, 2)
                ->nullable();

            $table->string('status', 50)
                ->default('Pending');

            $table->text('remarks')
                ->nullable();

            $table->string('duplicate_key', 64);

            $table->timestamps();

            $table->unique(
                [
                    'child_laborer_id',
                    'duplicate_key',
                ],
                'int_child_duplicate_unique'
            );

            $table->index(
                [
                    'child_laborer_id',
                    'status',
                ],
                'int_child_status_idx'
            );

            $table->index(
                [
                    'child_laborer_id',
                    'date_provided',
                ],
                'int_child_date_idx'
            );

            $table->index(
                'intervention_type',
                'int_type_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};