<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceIdleSessionTimeout
{
    private const SESSION_KEY =
        'clpmis_security_last_activity';

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        if (! $request->user()) {
            return $next($request);
        }

        $timeoutMinutes = max(
            0,
            (int) config(
                'clpmis-security.idle_timeout_minutes',
                30
            )
        );

        if ($timeoutMinutes === 0) {
            return $next($request);
        }

        $currentTimestamp = now()->timestamp;

        $lastActivity = (int) $request
            ->session()
            ->get(
                self::SESSION_KEY,
                $currentTimestamp
            );

        $timeoutSeconds =
            $timeoutMinutes * 60;

        if (
            $currentTimestamp
            - $lastActivity
            > $timeoutSeconds
        ) {
            Auth::guard('web')->logout();

            $request->session()
                ->invalidate();

            $request->session()
                ->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(
                    [
                        'message' =>
                            'Your session expired due to inactivity.',
                    ],
                    401
                );
            }

            return redirect()
                ->route('login')
                ->with(
                    'status',
                    'Your session expired after '
                    .$timeoutMinutes
                    .' minutes of inactivity. Please sign in again.'
                );
        }

        $request->session()->put(
            self::SESSION_KEY,
            $currentTimestamp
        );

        return $next($request);
    }
}
