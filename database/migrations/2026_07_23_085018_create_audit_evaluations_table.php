<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'audit_evaluations',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('audit_schedule_id')
                    ->constrained('audit_schedules')
                    ->cascadeOnDelete();

                /*
                 * Replaces the former auditor_id field.
                 */
                $table->foreignId('evaluated_by')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->foreignId('updated_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->date('evaluation_date');

                $table->longText('findings')
                    ->nullable();

                $table->longText('recommendations')
                    ->nullable();

                $table->string('status', 50)
                    ->default('Draft');

                $table->timestamp('submitted_at')
                    ->nullable();

                $table->timestamp('finalized_at')
                    ->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'audit_schedule_id',
                        'status',
                    ],
                    'audit_eval_schedule_status_idx'
                );

                $table->index(
                    [
                        'evaluated_by',
                        'evaluation_date',
                    ],
                    'audit_eval_user_date_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_evaluations');
    }
};