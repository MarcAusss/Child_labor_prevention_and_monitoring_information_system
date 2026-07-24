<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class SecurityStatusAccessTest extends TestCase
{
    public function test_guest_cannot_open_security_status(): void
    {
        $this->get(
            route('security.status')
        )->assertRedirect(
            route('login')
        );
    }
}
