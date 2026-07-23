<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_records', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('child_laborer_id')
                ->constrained('child_laborers')
                ->cascadeOnDelete();

            $table->string('school_name', 255)
                ->nullable();

            $table->string('grade_year_level', 100)
                ->nullable();

            $table->string('school_year', 20)
                ->nullable();

            $table->string('school_address', 500)
                ->nullable();

            $table->string('enrollment_status', 50);

            $table->text('reason_not_attending')
                ->nullable();

            $table->string('last_grade_completed', 150)
                ->nullable();

            $table->date('date_enrolled')
                ->nullable();

            $table->date('date_ended')
                ->nullable();

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
                'edu_child_duplicate_unique'
            );

            $table->index(
                [
                    'child_laborer_id',
                    'is_current',
                ],
                'edu_child_current_idx'
            );

            $table->index(
                [
                    'child_laborer_id',
                    'enrollment_status',
                ],
                'edu_child_status_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_records');
    }
};