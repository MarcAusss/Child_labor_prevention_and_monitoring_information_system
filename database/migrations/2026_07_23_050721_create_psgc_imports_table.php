<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('psgc_imports', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('imported_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('version', 100);
            $table->string('source_filename');
            $table->text('source_url')->nullable();

            $table->string('file_sha256', 64);

            $table->enum('status', [
                'Running',
                'Completed',
                'Failed',
            ])->default('Running');

            $table->json('record_counts')->nullable();
            $table->longText('error_message')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index([
                'file_sha256',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('psgc_imports');
    }
};