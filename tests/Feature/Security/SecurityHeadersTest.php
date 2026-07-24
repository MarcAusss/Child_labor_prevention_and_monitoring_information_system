<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_web_response_contains_security_headers(): void
    {
        $response = $this->get('/login');

        $response
            ->assertHeader(
                'X-Content-Type-Options',
                'nosniff'
            )
            ->assertHeader(
                'X-Frame-Options',
                'DENY'
            )
            ->assertHeader(
                'Referrer-Policy',
                'strict-origin-when-cross-origin'
            );
    }
}
