<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('backup_runs')) {
            return;
        }

        Schema::create(
            'backup_runs',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string('backup_type')
                    ->default('full');

                $table->string('status')
                    ->default('pending');

                $table->string('disk')
                    ->default('local');

                $table->string('file_path')
                    ->nullable();

                $table->string('file_name')
                    ->nullable();

                $table->unsignedBigInteger('file_size')
                    ->nullable();

                $table->string(
                    'checksum_sha256',
                    64
                )->nullable();

                $table->json('manifest')
                    ->nullable();

                $table->text('error_message')
                    ->nullable();

                $table->timestamp('started_at')
                    ->nullable();

                $table->timestamp('completed_at')
                    ->nullable();

                $table->timestamp('verified_at')
                    ->nullable();

                $table->timestamp('pruned_at')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'status',
                    'created_at',
                ]);

                $table->index([
                    'created_by',
                    'created_at',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'backup_runs'
        );
    }
};
