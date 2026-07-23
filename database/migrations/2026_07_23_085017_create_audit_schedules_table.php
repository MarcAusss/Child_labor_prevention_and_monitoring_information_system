<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_schedules', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('child_laborer_id')
                ->constrained('child_laborers')
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            /*
             * This replaces the former auditor_id field.
             * The assigned user must be an Admin or Super Admin.
             */
            $table->foreignId('assigned_to')
                ->constrained('users')
                ->restrictOnDelete();

            $table->dateTime('scheduled_at');

            $table->string('location', 500)
                ->nullable();

            $table->string('status', 50)
                ->default('Scheduled');

            $table->text('remarks')
                ->nullable();

            $table->timestamp('started_at')
                ->nullable();

            $table->timestamp('completed_at')
                ->nullable();

            $table->timestamp('cancelled_at')
                ->nullable();

            $table->timestamps();

            $table->index(
                [
                    'child_laborer_id',
                    'scheduled_at',
                ],
                'audit_schedule_child_date_idx'
            );

            $table->index(
                [
                    'assigned_to',
                    'status',
                ],
                'audit_schedule_assigned_status_idx'
            );

            $table->index(
                [
                    'status',
                    'scheduled_at',
                ],
                'audit_schedule_status_date_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_schedules');
    }
};