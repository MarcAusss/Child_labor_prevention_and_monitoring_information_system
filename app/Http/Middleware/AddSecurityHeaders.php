<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $response = $next($request);

        $response->headers->set(
            'X-Content-Type-Options',
            'nosniff'
        );

        $response->headers->set(
            'X-Frame-Options',
            (string) config(
                'clpmis-security.headers.x_frame_options',
                'DENY'
            )
        );

        $response->headers->set(
            'Referrer-Policy',
            (string) config(
                'clpmis-security.headers.referrer_policy',
                'strict-origin-when-cross-origin'
            )
        );

        $response->headers->set(
            'Permissions-Policy',
            (string) config(
                'clpmis-security.headers.permissions_policy',
                'camera=(), microphone=(), geolocation=(), payment=(), usb=()'
            )
        );

        $response->headers->set(
            'Cross-Origin-Opener-Policy',
            'same-origin'
        );

        $response->headers->set(
            'X-Permitted-Cross-Domain-Policies',
            'none'
        );

        if (
            config(
                'clpmis-security.headers.content_security_policy_enabled',
                false
            )
        ) {
            $response->headers->set(
                'Content-Security-Policy',
                (string) config(
                    'clpmis-security.headers.content_security_policy'
                )
            );
        }

        if (
            app()->isProduction()
            && $request->isSecure()
        ) {
            $maxAge = max(
                0,
                (int) config(
                    'clpmis-security.headers.hsts_max_age',
                    31536000
                )
            );

            $response->headers->set(
                'Strict-Transport-Security',
                'max-age='.$maxAge
                .'; includeSubDomains'
            );
        }

        if (
            $request->user()
            && str_contains(
                strtolower(
                    (string) $response
                        ->headers
                        ->get(
                            'Content-Type',
                            ''
                        )
                ),
                'text/html'
            )
        ) {
            $response->headers->set(
                'Cache-Control',
                'no-store, private'
            );

            $response->headers->set(
                'Pragma',
                'no-cache'
            );
        }

        return $response;
    }
}
