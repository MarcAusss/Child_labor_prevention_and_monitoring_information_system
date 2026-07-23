<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Arr;

class AuthenticationActivitySubscriber
{
    public function __construct(
        private readonly ActivityLogger
            $activityLogger
    ) {
    }

    public function handleLogin(
        Login $event
    ): void {
        if (! $event->user instanceof User) {
            return;
        }

        $this->activityLogger->log(
            action: ActivityLog::ACTION_LOGIN,
            description:
                'User logged in successfully.',
            subject: $event->user,
            metadata: [
                'guard' => $event->guard,
                'remember' => $event->remember,
            ],
            actor: $event->user
        );
    }

    public function handleLogout(
        Logout $event
    ): void {
        if (! $event->user instanceof User) {
            return;
        }

        $this->activityLogger->log(
            action: ActivityLog::ACTION_LOGOUT,
            description:
                'User logged out.',
            subject: $event->user,
            metadata: [
                'guard' => $event->guard,
            ],
            actor: $event->user
        );
    }

    public function handleFailedLogin(
        Failed $event
    ): void {
        $identifier = Arr::get(
            $event->credentials,
            'email'
        ) ?? Arr::get(
            $event->credentials,
            'username'
        );

        $this->activityLogger->log(
            action:
                ActivityLog::ACTION_LOGIN_FAILED,

            description:
                'A login attempt failed.',

            subject:
                $event->user instanceof User
                    ? $event->user
                    : null,

            metadata: [
                'guard' => $event->guard,
                'identifier' => $identifier,
            ],

            actor:
                $event->user instanceof User
                    ? $event->user
                    : null
        );
    }

    /**
     * @return array<class-string, string>
     */
    public function subscribe(
        Dispatcher $events
    ): array {
        return [
            Login::class =>
                'handleLogin',

            Logout::class =>
                'handleLogout',

            Failed::class =>
                'handleFailedLogin',
        ];
    }
}