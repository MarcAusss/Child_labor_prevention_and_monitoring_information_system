<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateLastLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (
            ! isset($user->id)
            || ! Schema::hasTable('users')
        ) {
            return;
        }

        $updates = [];

        if (
            Schema::hasColumn(
                'users',
                'last_login_at'
            )
        ) {
            $updates['last_login_at'] = now();
        }

        if (
            Schema::hasColumn(
                'users',
                'last_login_ip'
            )
        ) {
            $updates['last_login_ip'] =
                request()->ip();
        }

        if ($updates === []) {
            return;
        }

        DB::table('users')
            ->where('id', $user->id)
            ->update($updates);

        $user->forceFill($updates);
    }
}
