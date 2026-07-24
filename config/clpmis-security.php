<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Idle Session Timeout
    |--------------------------------------------------------------------------
    |
    | Authenticated users are logged out after this number of inactive
    | minutes. Set the value to zero to disable idle-session expiry.
    |
    */

    'idle_timeout_minutes' => (int) env(
        'CLPMIS_IDLE_TIMEOUT_MINUTES',
        30
    ),

    /*
    |--------------------------------------------------------------------------
    | Password Policy
    |--------------------------------------------------------------------------
    */

    'password' => [
        'minimum_length' => (int) env(
            'CLPMIS_PASSWORD_MIN_LENGTH',
            10
        ),

        'require_letters' => (bool) env(
            'CLPMIS_PASSWORD_REQUIRE_LETTERS',
            true
        ),

        'require_mixed_case' => (bool) env(
            'CLPMIS_PASSWORD_REQUIRE_MIXED_CASE',
            true
        ),

        'require_numbers' => (bool) env(
            'CLPMIS_PASSWORD_REQUIRE_NUMBERS',
            true
        ),

        'require_symbols' => (bool) env(
            'CLPMIS_PASSWORD_REQUIRE_SYMBOLS',
            true
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Security Headers
    |--------------------------------------------------------------------------
    */

    'headers' => [
        'x_frame_options' => env(
            'CLPMIS_X_FRAME_OPTIONS',
            'DENY'
        ),

        'referrer_policy' => env(
            'CLPMIS_REFERRER_POLICY',
            'strict-origin-when-cross-origin'
        ),

        'permissions_policy' => env(
            'CLPMIS_PERMISSIONS_POLICY',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()'
        ),

        /*
         * CSP is disabled by default because existing Vite, Alpine,
         * inline print styles, and local development assets must first
         * be inventoried before enforcing a strict production policy.
         */
        'content_security_policy_enabled' => (bool) env(
            'CLPMIS_CSP_ENABLED',
            false
        ),

        'content_security_policy' => env(
            'CLPMIS_CSP',
            "default-src 'self'; img-src 'self' data: blob:; font-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; connect-src 'self' ws: wss:; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'"
        ),

        'hsts_max_age' => (int) env(
            'CLPMIS_HSTS_MAX_AGE',
            31536000
        ),
    ],
];
