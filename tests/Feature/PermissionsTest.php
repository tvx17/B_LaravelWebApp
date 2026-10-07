<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_permissions_are_available_through_laravel_authorization(): void
    {
        $permission = Permission::create(['name' => 'edit articles', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $user = User::factory()->create();

        $this->assertFalse($user->can('edit articles'));

        $user->assignRole($role);

        $this->assertTrue($user->hasRole('editor'));
        $this->assertTrue($user->can('edit articles'));

        $user->removeRole($role);

        $this->assertFalse($user->can('edit articles'));
    }

    public function test_direct_permissions_can_be_granted_and_revoked(): void
    {
        Permission::create(['name' => 'edit articles', 'guard_name' => 'web']);
        $user = User::factory()->create();

        $user->givePermissionTo('edit articles');
        $this->assertTrue($user->can('edit articles'));

        $user->revokePermissionTo('edit articles');
        $this->assertFalse($user->can('edit articles'));
    }

    public function test_permission_middleware_denies_users_without_permission(): void
    {
        Route::middleware(['web', 'permission:edit articles'])->get('/_test/permissions', fn () => 'Allowed');
        Permission::create(['name' => 'edit articles', 'guard_name' => 'web']);
        $user = User::factory()->create();

        $this->actingAs($user)->get('/_test/permissions')->assertForbidden();

        $user->givePermissionTo('edit articles');

        $this->get('/_test/permissions')->assertOk()->assertSee('Allowed');
    }
}
