<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildLaborImportAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_import_access_can_open_import_page(): void
    {
        $user = User::factory()->create(['can_import_child_laborers' => true]);
        $this->actingAs($user)->get('/admin/child-labor-imports')->assertOk();
    }
}
