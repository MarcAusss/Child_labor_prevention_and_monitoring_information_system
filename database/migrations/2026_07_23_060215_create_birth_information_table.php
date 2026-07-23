<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birth_information', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('child_laborer_id')
                ->unique()
                ->constrained('child_laborers')
                ->cascadeOnDelete();

            $table->foreignId('region_id')
                ->constrained('regions')
                ->restrictOnDelete();

            $table->foreignId('province_id')
                ->nullable()
                ->constrained('provinces')
                ->restrictOnDelete();

            $table->foreignId('locality_id')
                ->constrained('localities')
                ->restrictOnDelete();

            $table->foreignId('barangay_id')
                ->constrained('barangays')
                ->restrictOnDelete();

            $table->string('place_of_birth', 255)
                ->nullable();

            $table->timestamps();

            $table->index(
                [
                    'region_id',
                    'province_id',
                    'locality_id',
                    'barangay_id',
                ],
                'birth_location_lookup_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birth_information');
    }
};