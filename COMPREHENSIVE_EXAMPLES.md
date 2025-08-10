# Laravel Permission Plus - Comprehensive Examples

<p align="center">
	<img src="logo.png" alt="Laravel Permission Plus Logo" width="180" />
</p>

This document provides comprehensive examples of how to use Laravel Permission Plus package for managing permissions, roles, groups, and guards in your Laravel application.

## Table of Contents
1. [Basic Setup and Configuration](#basic-setup-and-configuration)
2. [User Management with Access Control](#user-management-with-access-control)
3. [Role Management](#role-management)
4. [Group Management](#group-management)
5. [Permission Management](#permission-management)
6. [Guard Management](#guard-management)
7. [Advanced Usage Patterns](#advanced-usage-patterns)
8. [Middleware Usage](#middleware-usage)
9. [Blade Templates](#blade-templates)
10. [API Integration](#api-integration)

## Basic Setup and Configuration

### 1. Install and Configure
```bash
composer require eslamfaroug/laravel-permission-plus
php artisan vendor:publish --provider="EslamFaroug\PermissionPlus\Providers\PermissionServiceProvider" --tag="permission-plus-config"
php artisan vendor:publish --provider="EslamFaroug\PermissionPlus\Providers\PermissionServiceProvider" --tag="permission-plus-migrations"
php artisan migrate
```

### 2. Add Trait to User Model
```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use EslamFaroug\PermissionPlus\Traits\HasAccessControl;

class User extends Authenticatable
{
    use HasAccessControl;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    // ... other methods
}
```

### 3. Configure Languages
```php
// config/permission-plus.php
return [
    'languages' => ['en', 'ar', 'fr'],
    // ... other config
];
```

## User Management with Access Control

### Assigning Roles and Permissions
```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function assignRole(Request $request, User $user)
    {
        // Assign single role
        $user->assignRole('editor');

        // Assign multiple roles
        $user->assignRole('editor', 'moderator');

        // Assign role by ID
        $user->assignRole(1);

        // Assign permissions directly
        $user->givePermissionTo('edit-post', 'delete-post');

        // Assign to groups
        $user->assignToGroups('content-team', 'marketing-team');

        return response()->json(['message' => 'Roles assigned successfully']);
    }

    public function removeRole(Request $request, User $user)
    {
        // Remove single role
        $user->removeRole('editor');

        // Remove multiple roles
        $user->removeRole('editor', 'moderator');

        // Revoke permissions
        $user->revokePermissionTo('delete-post');

        // Remove from groups
        $user->removeFromGroups('content-team');

        return response()->json(['message' => 'Roles removed successfully']);
    }

    public function checkAccess(User $user)
    {
        // Check roles
        if ($user->hasRole('admin')) {
            // User has admin role
        }

        // Check permissions
        if ($user->hasPermissionTo('edit-post')) {
            // User can edit posts
        }

        // Check group membership
        if ($user->inGroup('content-team')) {
            // User is in content team
        }

        // Get all user permissions grouped by guard
        $groupedPermissions = $user->getGroupedPermissionsByGuard();

        return response()->json([
            'hasAdminRole' => $user->hasRole('admin'),
            'canEditPosts' => $user->hasPermissionTo('edit-post'),
            'inContentTeam' => $user->inGroup('content-team'),
            'permissions' => $groupedPermissions
        ]);
    }
}
```

## Role Management

### Creating and Managing Roles
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        // Get all roles
        $roles = AccessControl()->roles()->list();

        // Get roles with permissions
        $rolesWithPermissions = AccessControl()->roles()->list([], ['permissions']);

        // Filter roles by key
        $adminRoles = AccessControl()->roles()->list(['key' => 'admin']);

        // Get query builder for pagination
        $query = AccessControl()->roles()->list([], [], false);
        $paginatedRoles = $query->paginate(15);

        return view('roles.index', compact('roles', 'paginatedRoles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name.en' => 'required|string|max:255',
            'name.ar' => 'required|string|max:255',
            'key' => 'required|string|unique:roles,key',
            'permissions' => 'array'
        ]);

        // Create role with permissions
        $role = AccessControl()->roles()->create([
            'name' => [
                'en' => $request->input('name.en'),
                'ar' => $request->input('name.ar')
            ],
            'key' => $request->input('key'),
            'permissions' => $request->input('permissions', [])
        ]);

        return response()->json([
            'message' => 'Role created successfully',
            'role' => $role->load('permissions')
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name.en' => 'required|string|max:255',
            'name.ar' => 'required|string|max:255',
            'permissions' => 'array'
        ]);

        // Update role with permissions
        $role = AccessControl()->roles()->update($id, [
            'name' => [
                'en' => $request->input('name.en'),
                'ar' => $request->input('name.ar')
            ],
            'permissions' => $request->input('permissions', [])
        ]);

        return response()->json([
            'message' => 'Role updated successfully',
            'role' => $role
        ]);
    }

    public function destroy($id)
    {
        $deleted = AccessControl()->roles()->delete($id);

        if ($deleted) {
            return response()->json(['message' => 'Role deleted successfully']);
        }

        return response()->json(['message' => 'Role not found'], 404);
    }

    public function bulkAssignPermissions(Request $request, $roleId)
    {
        $request->validate([
            'permissions' => 'required|array'
        ]);

        $role = AccessControl()->roles()->show($roleId);
        
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        // Sync permissions (replace all existing permissions)
        $role->permissions()->sync($request->input('permissions'));

        return response()->json([
            'message' => 'Permissions assigned successfully',
            'role' => $role->fresh('permissions')
        ]);
    }
}
```

## Group Management

### Creating and Managing Groups
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
        // Get all groups
        $groups = AccessControl()->groups()->list();

        // Get groups with roles and members
        $groupsWithRelations = AccessControl()->groups()->list([], ['roles', 'members']);

        // Filter groups by key
        $contentGroups = AccessControl()->groups()->list(['key' => 'content-team']);

        return view('groups.index', compact('groups', 'groupsWithRelations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name.en' => 'required|string|max:255',
            'name.ar' => 'required|string|max:255',
            'key' => 'required|string|unique:groups,key',
            'description.en' => 'nullable|string',
            'description.ar' => 'nullable|string',
            'roles' => 'array'
        ]);

        // Create group with roles
        $group = AccessControl()->groups()->create([
            'name' => [
                'en' => $request->input('name.en'),
                'ar' => $request->input('name.ar')
            ],
            'key' => $request->input('key'),
            'description' => [
                'en' => $request->input('description.en'),
                'ar' => $request->input('description.ar')
            ],
            'roles' => $request->input('roles', [])
        ]);

        return response()->json([
            'message' => 'Group created successfully',
            'group' => $group->load('roles')
        ]);
    }

    public function addMembers(Request $request, $groupId)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $group = AccessControl()->groups()->show($groupId);
        
        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        // Add users to group
        foreach ($request->input('user_ids') as $userId) {
            $user = User::find($userId);
            $user->assignToGroups($group->key);
        }

        return response()->json([
            'message' => 'Members added successfully',
            'group' => $group->fresh('members')
        ]);
    }

    public function removeMembers(Request $request, $groupId)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $group = AccessControl()->groups()->show($groupId);
        
        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        // Remove users from group
        foreach ($request->input('user_ids') as $userId) {
            $user = User::find($userId);
            $user->removeFromGroups($group->key);
        }

        return response()->json([
            'message' => 'Members removed successfully'
        ]);
    }
}
```

## Permission Management

### Creating and Managing Permissions
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        // Get all permissions
        $permissions = AccessControl()->guards()->list([], ['permissions']);

        // Get permissions by guard
        $webPermissions = AccessControl()->guards()->show('web', ['permissions']);

        return view('permissions.index', compact('permissions', 'webPermissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name.en' => 'required|string|max:255',
            'name.ar' => 'required|string|max:255',
            'key' => 'required|string|unique:permissions,key',
            'guard_key' => 'required|exists:permission_guards,key'
        ]);

        // Find guard
        $guard = AccessControl()->guards()->show($request->input('guard_key'));

        // Create permission under guard
        $permission = $guard->permissions()->create([
            'name' => [
                'en' => $request->input('name.en'),
                'ar' => $request->input('name.ar')
            ],
            'key' => $request->input('key')
        ]);

        return response()->json([
            'message' => 'Permission created successfully',
            'permission' => $permission->load('permissionGuard')
        ]);
    }

    public function bulkCreate(Request $request)
    {
        $request->validate([
            'guard_key' => 'required|exists:permission_guards,key',
            'permissions' => 'required|array',
            'permissions.*.name.en' => 'required|string|max:255',
            'permissions.*.name.ar' => 'required|string|max:255',
            'permissions.*.key' => 'required|string|unique:permissions,key'
        ]);

        $guard = AccessControl()->guards()->show($request->input('guard_key'));
        $createdPermissions = [];

        foreach ($request->input('permissions') as $permissionData) {
            $permission = $guard->permissions()->create($permissionData);
            $createdPermissions[] = $permission;
        }

        return response()->json([
            'message' => 'Permissions created successfully',
            'permissions' => $createdPermissions
        ]);
    }
}
```

## Guard Management

### Creating and Managing Guards
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GuardController extends Controller
{
    public function index()
    {
        // Get all guards
        $guards = AccessControl()->guards()->list();

        // Get guards with permissions
        $guardsWithPermissions = AccessControl()->guards()->list([], ['permissions']);

        return view('guards.index', compact('guards', 'guardsWithPermissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name.en' => 'required|string|max:255',
            'name.ar' => 'required|string|max:255',
            'key' => 'required|string|unique:permission_guards,key',
            'permissions' => 'array'
        ]);

        // Create guard with permissions
        $guard = AccessControl()->guards()->create([
            'name' => [
                'en' => $request->input('name.en'),
                'ar' => $request->input('name.ar')
            ],
            'key' => $request->input('key'),
            'permissions' => $request->input('permissions', [])
        ]);

        return response()->json([
            'message' => 'Guard created successfully',
            'guard' => $guard->load('permissions')
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name.en' => 'required|string|max:255',
            'name.ar' => 'required|string|max:255',
            'permissions' => 'array'
        ]);

        // Update guard with permissions
        $guard = AccessControl()->guards()->update($id, [
            'name' => [
                'en' => $request->input('name.en'),
                'ar' => $request->input('name.ar')
            ],
            'permissions' => $request->input('permissions', [])
        ]);

        return response()->json([
            'message' => 'Guard updated successfully',
            'guard' => $guard
        ]);
    }
}
```

## Advanced Usage Patterns

### Complex Queries and Relationships
```php
<?php

namespace App\Services;

class AccessControlService
{
    public function getUsersWithSpecificPermissions($permissionKeys)
    {
        // Get users who have specific permissions
        $users = User::whereHas('permissions', function($query) use ($permissionKeys) {
            $query->whereIn('key', $permissionKeys);
        })->get();

        return $users;
    }

    public function getRolesByPermissionGuard($guardKey)
    {
        // Get roles that have permissions from specific guard
        $query = AccessControl()->roles()->list([], ['permissions'], false);
        $roles = $query->whereHas('permissions.permissionGuard', function($query) use ($guardKey) {
            $query->where('key', $guardKey);
        })->get();

        return $roles;
    }

    public function getGroupsWithRoleHierarchy()
    {
        // Get groups with their roles and permissions
        $groups = AccessControl()->groups()->list([], ['roles.permissions.permissionGuard']);

        // Group permissions by guard for each group
        foreach ($groups as $group) {
            $group->permissions_by_guard = [];
            
            foreach ($group->roles as $role) {
                foreach ($role->permissions as $permission) {
                    $guardKey = $permission->permissionGuard->key;
                    
                    if (!isset($group->permissions_by_guard[$guardKey])) {
                        $group->permissions_by_guard[$guardKey] = [];
                    }
                    
                    $group->permissions_by_guard[$guardKey][] = $permission;
                }
            }
        }

        return $groups;
    }

    public function bulkAssignRolesToUsers($userIds, $roleKeys)
    {
        $users = User::whereIn('id', $userIds)->get();
        $roles = AccessControl()->roles()->list(['key' => $roleKeys]);

        foreach ($users as $user) {
            $user->assignRole($roles->pluck('key')->toArray());
        }

        return $users;
    }

    public function getPermissionMatrix()
    {
        // Create a matrix showing which roles have which permissions
        $roles = AccessControl()->roles()->list([], ['permissions.permissionGuard']);
        $matrix = [];

        foreach ($roles as $role) {
            $matrix[$role->key] = [
                'name' => $role->name,
                'permissions' => []
            ];

            foreach ($role->permissions as $permission) {
                $guardKey = $permission->permissionGuard->key;
                
                if (!isset($matrix[$role->key]['permissions'][$guardKey])) {
                    $matrix[$role->key]['permissions'][$guardKey] = [];
                }
                
                $matrix[$role->key]['permissions'][$guardKey][] = [
                    'key' => $permission->key,
                    'name' => $permission->name
                ];
            }
        }

        return $matrix;
    }
}
```

## Middleware Usage

### Custom Middleware
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!auth()->check()) {
            return redirect('login');
        }

        $user = auth()->user();

        if (!$user->hasPermissionTo($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!auth()->check()) {
            return redirect('login');
        }

        $user = auth()->user();

        if (!$user->hasRole($role)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}

class CheckGroup
{
    public function handle(Request $request, Closure $next, $group)
    {
        if (!auth()->check()) {
            return redirect('login');
        }

        $user = auth()->user();

        if (!$user->inGroup($group)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
```

### Route Protection
```php
<?php

// routes/web.php

Route::middleware(['auth'])->group(function () {
    // Routes that require specific permissions
    Route::middleware(['permission:edit-post'])->group(function () {
        Route::get('/posts/{post}/edit', [PostController::class, 'edit']);
        Route::put('/posts/{post}', [PostController::class, 'update']);
    });

    // Routes that require specific roles
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
    });

    // Routes that require group membership
    Route::middleware(['group:content-team'])->group(function () {
        Route::get('/content/dashboard', [ContentController::class, 'dashboard']);
    });
});
```

## Blade Templates

### Permission Checks in Views
```blade
{{-- resources/views/posts/show.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $post->title }}</h1>
    <p>{{ $post->content }}</p>

    {{-- Check permissions --}}
    @if(auth()->user()->hasPermissionTo('edit-post'))
        <a href="{{ route('posts.edit', $post) }}" class="btn btn-primary">Edit Post</a>
    @endif

    @if(auth()->user()->hasPermissionTo('delete-post'))
        <form action="{{ route('posts.destroy', $post) }}" method="POST" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">
                Delete Post
            </button>
        </form>
    @endif

    {{-- Check roles --}}
    @if(auth()->user()->hasRole('admin'))
        <div class="admin-actions">
            <h3>Admin Actions</h3>
            <a href="{{ route('posts.moderate', $post) }}" class="btn btn-warning">Moderate</a>
        </div>
    @endif

    {{-- Check group membership --}}
    @if(auth()->user()->inGroup('content-team'))
        <div class="content-team-actions">
            <h3>Content Team Actions</h3>
            <a href="{{ route('posts.review', $post) }}" class="btn btn-info">Review</a>
        </div>
    @endif
</div>
@endsection
```

### Dynamic Menu Generation
```blade
{{-- resources/views/layouts/navigation.blade.php --}}

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">{{ config('app.name', 'Laravel') }}</a>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}">Home</a>
                </li>

                {{-- Show posts menu if user can view posts --}}
                @if(auth()->user()->hasPermissionTo('view-post'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('posts.index') }}">Posts</a>
                    </li>
                @endif

                {{-- Show admin menu if user has admin role --}}
                @if(auth()->user()->hasRole('admin'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                            Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('users.index') }}">Users</a></li>
                            <li><a class="dropdown-item" href="{{ route('roles.index') }}">Roles</a></li>
                            <li><a class="dropdown-item" href="{{ route('groups.index') }}">Groups</a></li>
                        </ul>
                    </li>
                @endif

                {{-- Show content team menu if user is in content team --}}
                @if(auth()->user()->inGroup('content-team'))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="contentDropdown" role="button" data-bs-toggle="dropdown">
                            Content
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('content.dashboard') }}">Dashboard</a></li>
                            <li><a class="dropdown-item" href="{{ route('content.schedule') }}">Schedule</a></li>
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
```

## API Integration

### API Controllers
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserApiController extends Controller
{
    public function getPermissions(User $user)
    {
        // Get user permissions grouped by guard
        $permissions = $user->getGroupedPermissionsByGuard();

        return response()->json([
            'user_id' => $user->id,
            'permissions' => $permissions,
            'roles' => $user->roles->map(function($role) {
                return [
                    'id' => $role->id,
                    'key' => $role->key,
                    'name' => $role->name
                ];
            }),
            'groups' => $user->groups->map(function($group) {
                return [
                    'id' => $group->id,
                    'key' => $group->key,
                    'name' => $group->name
                ];
            })
        ]);
    }

    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,key'
        ]);

        $user->assignRole($request->input('role'));

        return response()->json([
            'message' => 'Role assigned successfully',
            'user' => $user->fresh('roles')
        ]);
    }

    public function bulkAssign(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'array',
            'permissions' => 'array',
            'groups' => 'array'
        ]);

        if ($request->has('roles')) {
            $user->assignRole($request->input('roles'));
        }

        if ($request->has('permissions')) {
            $user->givePermissionTo($request->input('permissions'));
        }

        if ($request->has('groups')) {
            $user->assignToGroups($request->input('groups'));
        }

        return response()->json([
            'message' => 'Access control updated successfully',
            'user' => $user->fresh(['roles', 'permissions', 'groups'])
        ]);
    }
}
```

### API Routes
```php
// routes/api.php

Route::middleware(['auth:sanctum'])->group(function () {
    // User permissions API
    Route::get('/users/{user}/permissions', [UserApiController::class, 'getPermissions']);
    Route::post('/users/{user}/roles', [UserApiController::class, 'assignRole']);
    Route::put('/users/{user}/access', [UserApiController::class, 'bulkAssign']);

    // Role management API
    Route::apiResource('roles', RoleApiController::class);
    Route::post('/roles/{role}/permissions', [RoleApiController::class, 'assignPermissions']);

    // Group management API
    Route::apiResource('groups', GroupApiController::class);
    Route::post('/groups/{group}/members', [GroupApiController::class, 'addMembers']);
    Route::delete('/groups/{group}/members', [GroupApiController::class, 'removeMembers']);
});
```

## Testing Examples

### Feature Tests
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_assigned_role()
    {
        $user = User::factory()->create();
        $role = AccessControl()->roles()->create([
            'name' => ['en' => 'Editor', 'ar' => 'محرر'],
            'key' => 'editor'
        ]);

        $user->assignRole('editor');

        $this->assertTrue($user->hasRole('editor'));
        $this->assertTrue($user->hasRole($role->id));
    }

    public function test_user_can_be_assigned_permission()
    {
        $user = User::factory()->create();
        $guard = AccessControl()->guards()->create([
            'name' => ['en' => 'Web', 'ar' => 'ويب'],
            'key' => 'web'
        ]);

        $permission = $guard->permissions()->create([
            'name' => ['en' => 'Edit Post', 'ar' => 'تعديل المقال'],
            'key' => 'edit-post'
        ]);

        $user->givePermissionTo('edit-post');

        $this->assertTrue($user->hasPermissionTo('edit-post'));
    }

    public function test_user_can_be_assigned_to_group()
    {
        $user = User::factory()->create();
        $group = AccessControl()->groups()->create([
            'name' => ['en' => 'Content Team', 'ar' => 'فريق المحتوى'],
            'key' => 'content-team'
        ]);

        $user->assignToGroups('content-team');

        $this->assertTrue($user->inGroup('content-team'));
    }

    public function test_role_can_have_permissions()
    {
        $role = AccessControl()->roles()->create([
            'name' => ['en' => 'Editor', 'ar' => 'محرر'],
            'key' => 'editor'
        ]);

        $guard = AccessControl()->guards()->create([
            'name' => ['en' => 'Web', 'ar' => 'ويب'],
            'key' => 'web'
        ]);

        $permission = $guard->permissions()->create([
            'name' => ['en' => 'Edit Post', 'ar' => 'تعديل المقال'],
            'key' => 'edit-post'
        ]);

        $role->permissions()->attach($permission->id);

        $this->assertTrue($role->permissions->contains($permission));
    }
}
```

## Best Practices

### 1. Always Use AccessControl API
```php
// ✅ Good - Use AccessControl API
$roles = AccessControl()->roles()->list();

// ❌ Bad - Direct model access
$roles = Role::all();
```

### 2. Use Keys for Identification
```php
// ✅ Good - Use keys for better maintainability
$user->assignRole('admin');
$user->hasPermissionTo('edit-post');

// ❌ Bad - Hard-coded IDs
$user->assignRole(1);
$user->hasPermissionTo(5);
```

### 3. Load Relations When Needed
```php
// ✅ Good - Load only needed relations
$roles = AccessControl()->roles()->list([], ['permissions']);

// ❌ Bad - Load all relations
$roles = Role::with(['permissions', 'groups', 'users'])->get();
```

### 4. Handle Multilingual Content Properly
```php
// ✅ Good - Provide all language versions
$role = AccessControl()->roles()->create([
    'name' => [
        'en' => 'Editor',
        'ar' => 'محرر',
        'fr' => 'Éditeur'
    ],
    'key' => 'editor'
]);

// ❌ Bad - Single language only
$role = AccessControl()->roles()->create([
    'name' => 'Editor',
    'key' => 'editor'
]);
```

### 5. Use Middleware for Route Protection
```php
// ✅ Good - Use middleware
Route::middleware(['permission:edit-post'])->group(function () {
    Route::put('/posts/{post}', [PostController::class, 'update']);
});

// ❌ Bad - Check in controller
public function update(Request $request, Post $post)
{
    if (!auth()->user()->hasPermissionTo('edit-post')) {
        abort(403);
    }
    // ... rest of the method
}
```

This comprehensive guide covers all aspects of using Laravel Permission Plus package. The examples demonstrate best practices and common use cases for building robust access control systems in Laravel applications.
