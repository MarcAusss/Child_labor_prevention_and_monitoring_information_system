<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_guardians', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('child_laborer_id')
                ->constrained('child_laborers')
                ->cascadeOnDelete();

            $table->string('full_name', 200);
            $table->string('relationship', 100);

            $table->string('contact_number', 30)
                ->nullable();

            $table->string('occupation', 150)
                ->nullable();

            $table->string('educational_attainment', 150)
                ->nullable();

            $table->decimal('monthly_income', 12, 2)
                ->nullable();

            $table->boolean('is_primary')
                ->default(false);

            $table->timestamps();

            $table->index(
                [
                    'child_laborer_id',
                    'is_primary',
                ],
                'pg_child_primary_idx'
            );

            $table->index(
                [
                    'child_laborer_id',
                    'full_name',
                ],
                'pg_child_name_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_guardians');
    }
};