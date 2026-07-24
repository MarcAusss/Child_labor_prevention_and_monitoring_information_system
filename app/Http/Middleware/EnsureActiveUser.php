<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (
            ! $user
            || ! array_key_exists(
                'is_active',
                $user->getAttributes()
            )
            || (bool) $user->is_active
        ) {
            return $next($request);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()
            ->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(
                [
                    'message' =>
                        'This user account is inactive.',
                ],
                403
            );
        }

        return redirect()
            ->route('login')
            ->withErrors([
                'email' =>
                    'This account is inactive. Contact a system administrator.',
            ]);
    }
}
