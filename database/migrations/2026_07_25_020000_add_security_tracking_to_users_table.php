<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn(
                'users',
                'last_login_at'
            )) {
                $table->timestamp('last_login_at')
                    ->nullable()
                    ->after('remember_token');
            }

            if (! Schema::hasColumn(
                'users',
                'last_login_ip'
            )) {
                $table->string(
                    'last_login_ip',
                    45
                )
                    ->nullable()
                    ->after('last_login_at');
            }

            if (! Schema::hasColumn(
                'users',
                'password_changed_at'
            )) {
                $table->timestamp(
                    'password_changed_at'
                )
                    ->nullable()
                    ->after('last_login_ip');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $columns = collect([
            'last_login_at',
            'last_login_ip',
            'password_changed_at',
        ])->filter(
            fn (string $column): bool =>
                Schema::hasColumn(
                    'users',
                    $column
                )
        )->all();

        if ($columns === []) {
            return;
        }

        Schema::table('users', function (
            Blueprint $table
        ) use ($columns): void {
            $table->dropColumn($columns);
        });
    }
};
