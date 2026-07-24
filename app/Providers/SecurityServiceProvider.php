<?php

namespace App\Providers;

use App\Console\Commands\ClpmisSecurityCheck;
use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\EnforceIdleSessionTimeout;
use App\Http\Middleware\EnsureActiveUser;
use App\Listeners\UpdateLastLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class SecurityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(
        Router $router
    ): void {
        $router->aliasMiddleware(
            'active.user',
            EnsureActiveUser::class
        );

        $router->aliasMiddleware(
            'idle.timeout',
            EnforceIdleSessionTimeout::class
        );

        $router->pushMiddlewareToGroup(
            'web',
            AddSecurityHeaders::class
        );

        $router->pushMiddlewareToGroup(
            'web',
            EnsureActiveUser::class
        );

        $router->pushMiddlewareToGroup(
            'web',
            EnforceIdleSessionTimeout::class
        );

        Event::listen(
            Login::class,
            UpdateLastLogin::class
        );

        Password::defaults(
            function (): Password {
                $rule = Password::min(
                    max(
                        8,
                        (int) config(
                            'clpmis-security.password.minimum_length',
                            10
                        )
                    )
                );

                if (
                    config(
                        'clpmis-security.password.require_letters',
                        true
                    )
                ) {
                    $rule->letters();
                }

                if (
                    config(
                        'clpmis-security.password.require_mixed_case',
                        true
                    )
                ) {
                    $rule->mixedCase();
                }

                if (
                    config(
                        'clpmis-security.password.require_numbers',
                        true
                    )
                ) {
                    $rule->numbers();
                }

                if (
                    config(
                        'clpmis-security.password.require_symbols',
                        true
                    )
                ) {
                    $rule->symbols();
                }

                return $rule;
            }
        );

        Route::middleware('web')
            ->group(
                base_path(
                    'routes/security.php'
                )
            );

        if (
            $this->app
                ->runningInConsole()
        ) {
            $this->commands([
                ClpmisSecurityCheck::class,
            ]);
        }
    }
}
