<?php

namespace EslamFaroug\PermissionPlus\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use EslamFaroug\PermissionPlus\Models\Group;
use EslamFaroug\PermissionPlus\Models\Role;
use EslamFaroug\PermissionPlus\Models\Permission;
use EslamFaroug\PermissionPlus\Models\PermissionGuard;
use EslamFaroug\PermissionPlus\Tests\TestCase;
use App\Models\User;

class PermissionPlusFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_assign_and_check_role_permission_group()
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => ['en'=>'Editor'], 'key'=>'editor']);
        $perm = Permission::create(['name' => ['en'=>'Edit'], 'key'=>'edit-post']);
        $group = Group::create(['name' => ['en'=>'Team'], 'key'=>'team']);
        $role->permissions()->attach($perm);
        $user->assignRole('editor');
        $user->assignToGroups('team');
        $this->assertTrue($user->hasRole('editor'));
        $this->assertTrue($user->hasPermissionTo('edit-post'));
        $this->assertTrue($user->inGroup('team'));
    }

    public function test_translatable_fields_work()
    {
        $role = Role::create(['name' => ['en'=>'Admin', 'ar'=>'مشرف'], 'key'=>'admin']);
        app()->setLocale('ar');
        $this->assertEquals('مشرف', $role->name);
        app()->setLocale('en');
        $this->assertEquals('Admin', $role->name);
        $all = $role->getTranslations('name');
        $this->assertEquals(['en'=>'Admin','ar'=>'مشرف'], $all);
    }

    public function test_gate_auto_registers_permissions()
    {
        $user = User::factory()->create();
        $perm = Permission::create(['name'=>['en'=>'Delete'],'key'=>'delete-post']);
        $user->assignPermission('delete-post');
        $this->assertTrue(Gate::forUser($user)->allows('delete-post'));
    }

    public function test_facade_and_helper_access()
    {
        $roles = \AccessControl()->roles()->all();
        $groups = \AccessControl()->groups()->all();
        $this->assertIsArray($roles);
        $this->assertIsArray($groups);
    }
}
