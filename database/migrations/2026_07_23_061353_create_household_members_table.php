<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('household_members', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('child_laborer_id')
                ->constrained('child_laborers')
                ->cascadeOnDelete();

            $table->string('full_name', 200);
            $table->string('relationship', 100);

            $table->enum('sex', [
                'Male',
                'Female',
            ]);

            $table->date('birth_date')
                ->nullable();

            $table->string('civil_status', 50)
                ->nullable();

            $table->string('educational_attainment', 150)
                ->nullable();

            $table->string('occupation', 150)
                ->nullable();

            $table->decimal('monthly_income', 12, 2)
                ->nullable();

            $table->string('duplicate_key', 64);

            $table->timestamps();

            $table->unique(
                [
                    'child_laborer_id',
                    'duplicate_key',
                ],
                'hh_child_duplicate_unique'
            );

            $table->index(
                [
                    'child_laborer_id',
                    'birth_date',
                ],
                'hh_child_birth_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_members');
    }
};