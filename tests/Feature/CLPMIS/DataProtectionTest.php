<?php

namespace Tests\Feature\CLPMIS;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesClpmisRecords;
use Tests\TestCase;

class DataProtectionTest extends TestCase
{
    use RefreshDatabase;
    use CreatesClpmisRecords;

    public function test_private_document_disk_is_outside_public_directory(): void
    {
        $root = config(
            'filesystems.disks.clpmis_documents.root'
        );

        $this->assertIsString($root);

        $normalizedRoot = strtolower(
            str_replace(
                '\\',
                '/',
                realpath($root) ?: $root
            )
        );

        $normalizedPublic = strtolower(
            str_replace(
                '\\',
                '/',
                realpath(public_path())
                    ?: public_path()
            )
        );

        $this->assertFalse(
            str_starts_with(
                $normalizedRoot,
                $normalizedPublic
            ),
            'Private documents must not be stored inside public/.'
        );
    }

    public function test_security_headers_are_present(): void
    {
        $this->get('/login')
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

    public function test_authenticated_html_is_not_publicly_cached(): void
    {
        $user = $this->makeUser(
            'viewer'
        );

        $response = $this
            ->actingAs($user)
            ->get(
                route(
                    'workspace.dashboard'
                )
            );

        $response->assertOk();

        $this->assertStringContainsString(
            'no-store',
            strtolower(
                (string) $response
                    ->headers
                    ->get(
                        'Cache-Control'
                    )
            )
        );
    }
}
