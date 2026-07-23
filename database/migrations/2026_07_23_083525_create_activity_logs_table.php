<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'activity_logs',
            function (Blueprint $table): void {
                $table->id();

                /*
                 * Nullable so failed-login and system actions
                 * can still be recorded without an authenticated
                 * user.
                 */
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                /*
                 * Snapshot of the actor's name and role at the
                 * time of the action.
                 */
                $table->string(
                    'actor_name',
                    255
                )->nullable();

                $table->string(
                    'role_name',
                    100
                )->nullable();

                /*
                 * Links profile-related activity directly to the
                 * child laborer while retaining a generic affected
                 * entity reference.
                 */
                $table->foreignId('child_laborer_id')
                    ->nullable()
                    ->constrained('child_laborers')
                    ->nullOnDelete();

                $table->string(
                    'action',
                    100
                );

                $table->string(
                    'entity_type',
                    255
                )->nullable();

                $table->unsignedBigInteger(
                    'entity_id'
                )->nullable();

                $table->text(
                    'description'
                );

                $table->json(
                    'old_values'
                )->nullable();

                $table->json(
                    'new_values'
                )->nullable();

                $table->json(
                    'metadata'
                )->nullable();

                $table->string(
                    'ip_address',
                    45
                )->nullable();

                $table->text(
                    'user_agent'
                )->nullable();

                $table->string(
                    'request_method',
                    10
                )->nullable();

                $table->string(
                    'route_name',
                    255
                )->nullable();

                $table->text(
                    'url'
                )->nullable();

                /*
                 * Activity records are immutable and only require
                 * a creation timestamp.
                 */
                $table->timestamp(
                    'created_at'
                )->useCurrent();

                $table->index(
                    [
                        'user_id',
                        'created_at',
                    ],
                    'activity_user_date_idx'
                );

                $table->index(
                    [
                        'child_laborer_id',
                        'created_at',
                    ],
                    'activity_child_date_idx'
                );

                $table->index(
                    [
                        'action',
                        'created_at',
                    ],
                    'activity_action_date_idx'
                );

                $table->index(
                    [
                        'entity_type',
                        'entity_id',
                    ],
                    'activity_entity_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'activity_logs'
        );
    }
};