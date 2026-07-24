<?php

namespace Tests\Unit\CLPMIS;

use Tests\TestCase;

class SecurityConfigurationTest extends TestCase
{
    public function test_password_policy_is_strong_by_default(): void
    {
        $this->assertGreaterThanOrEqual(
            10,
            (int) config(
                'clpmis-security.password.minimum_length'
            )
        );

        $this->assertTrue(
            (bool) config(
                'clpmis-security.password.require_letters'
            )
        );

        $this->assertTrue(
            (bool) config(
                'clpmis-security.password.require_mixed_case'
            )
        );

        $this->assertTrue(
            (bool) config(
                'clpmis-security.password.require_numbers'
            )
        );

        $this->assertTrue(
            (bool) config(
                'clpmis-security.password.require_symbols'
            )
        );
    }

    public function test_idle_timeout_is_enabled(): void
    {
        $this->assertGreaterThan(
            0,
            (int) config(
                'clpmis-security.idle_timeout_minutes'
            )
        );
    }
}
