<?php

namespace Tests\Feature\Backup;

use Tests\TestCase;

class BackupAccessTest extends TestCase
{
    public function test_guest_cannot_open_backup_management(): void
    {
        $this->get(
            route('backups.index')
        )->assertRedirect(
            route('login')
        );
    }
}
