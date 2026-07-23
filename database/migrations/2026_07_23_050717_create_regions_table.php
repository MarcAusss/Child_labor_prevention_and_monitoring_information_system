<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table): void {
            $table->id();

            $table->string('psgc_code', 10)->unique();
            $table->string('correspondence_code', 9)->nullable();
            $table->string('name', 200);
            $table->text('old_names')->nullable();
            $table->unsignedBigInteger('population')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index([
                'name',
                'is_active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};