<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employment_records', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('child_laborer_id')
                ->constrained('child_laborers')
                ->cascadeOnDelete();

            $table->string('employer_name', 255)
                ->nullable();

            $table->string('employer_address', 500)
                ->nullable();

            $table->string('work_type', 100);

            $table->string('occupation', 200);

            $table->string('industry', 150)
                ->nullable();

            $table->string('employment_arrangement', 100);

            $table->date('start_date');

            $table->date('end_date')
                ->nullable();

            $table->unsignedTinyInteger('days_per_week');

            $table->decimal('hours_per_day', 4, 2);

            $table->decimal('income_amount', 12, 2)
                ->nullable();

            $table->string('income_frequency', 50);

            $table->boolean('is_current')
                ->default(false);

            $table->text('remarks')
                ->nullable();

            $table->string('duplicate_key', 64);

            $table->timestamps();

            $table->unique(
                [
                    'child_laborer_id',
                    'duplicate_key',
                ],
                'emp_child_duplicate_unique'
            );

            $table->index(
                [
                    'child_laborer_id',
                    'is_current',
                ],
                'emp_child_current_idx'
            );

            $table->index(
                [
                    'child_laborer_id',
                    'start_date',
                ],
                'emp_child_start_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_records');
    }
};