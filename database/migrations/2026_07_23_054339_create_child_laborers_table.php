<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_laborers', function (Blueprint $table): void {
            $table->id();

            $table->string('profile_number', 30)
                ->nullable()
                ->unique();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Personal information
            |--------------------------------------------------------------------------
            */

            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('suffix', 20)->nullable();

            $table->enum('sex', [
                'Male',
                'Female',
            ]);

            $table->date('birth_date');

            $table->string('civil_status', 50)->nullable();
            $table->string('nationality', 100)->default('Filipino');
            $table->string('religion', 150)->nullable();
            $table->string('contact_number', 30)->nullable();

            $table->string('photo_path')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Duplicate detection
            |--------------------------------------------------------------------------
            */

            $table->string('duplicate_key', 64)->unique();

            /*
            |--------------------------------------------------------------------------
            | Profile workflow
            |--------------------------------------------------------------------------
            */

            $table->string('status', 30)->default('Draft');

            $table->string('status_before_archive', 30)->nullable();

            $table->text('return_reason')->nullable();
            $table->text('archive_reason')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('archived_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'status',
                'created_at',
            ]);

            $table->index([
                'created_by',
                'status',
            ]);

            $table->index([
                'assigned_to',
                'status',
            ]);

            $table->index([
                'last_name',
                'first_name',
                'birth_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_laborers');
    }
};