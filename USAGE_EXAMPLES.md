
# Laravel Permission Plus - Usage Examples

<p align="center">
	<img src="logo.png" alt="Laravel Permission Plus Logo" width="180" />
</p>

This document provides practical examples of how to use Laravel Permission Plus package for common access control scenarios.

## Quick Start Examples

### Basic User Setup
```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use EslamFaroug\PermissionPlus\Traits\HasAccessControl;

class User extends Authenticatable
{
    use HasAccessControl;

    protected $fillable = ['name', 'email', 'password'];
}
```

### Assigning Access Control
```php
// Assign roles to user
$user->assignRole('editor');
$user->assignRole('moderator', 'admin');

// Assign permissions directly
$user->givePermissionTo('edit-post');
$user->givePermissionTo('delete-post', 'publish-post');

// Assign to groups
$user->assignToGroups('content-team');
$user->assignToGroups('marketing-team', 'development-team');
```

### Checking Access Control
```php
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
```

## Role Management Examples

### Creating Roles
```php
// Basic role creation
$role = AccessControl()->roles()->create([
    'name' => ['en' => 'Editor', 'ar' => 'محرر'],
    'key' => 'editor'
]);

// Role with permissions
$role = AccessControl()->roles()->create([
    'name' => ['en' => 'Content Manager', 'ar' => 'مدير المحتوى'],
    'key' => 'content-manager',
    'permissions' => ['create-post', 'edit-post', 'delete-post']
]);
```

### Managing Role Permissions
```php
// Get role with permissions
$role = AccessControl()->roles()->show('admin', ['permissions']);

// Add permissions to role
$role->permissions()->attach([1, 2, 3]);

// Sync permissions (replace all existing)
$role->permissions()->sync(['edit-post', 'delete-post', 'publish-post']);

// Remove specific permissions
$role->permissions()->detach(['delete-post']);
```

## Group Management Examples

### Creating Groups
```php
// Basic group creation
$group = AccessControl()->groups()->create([
    'name' => ['en' => 'Content Team', 'ar' => 'فريق المحتوى'],
    'key' => 'content-team',
    'description' => ['en' => 'Team for content creation', 'ar' => 'فريق إنشاء المحتوى']
]);

// Group with roles
$group = AccessControl()->groups()->create([
    'name' => ['en' => 'Marketing Team', 'ar' => 'فريق التسويق'],
    'key' => 'marketing-team',
    'roles' => ['marketer', 'designer', 'analyst']
]);
```

### Managing Group Members
```php
// Add users to group
$user->assignToGroups('content-team');

// Remove users from group
$user->removeFromGroups('content-team');

// Check group membership
if ($user->inGroup('content-team')) {
    // User is in content team
}
```

## Permission Management Examples

### Creating Permissions
```php
// Create guard first
$guard = AccessControl()->guards()->create([
    'name' => ['en' => 'Web Guard', 'ar' => 'حارس الويب'],
    'key' => 'web'
]);

// Create permission under guard
$permission = $guard->permissions()->create([
    'name' => ['en' => 'Edit Post', 'ar' => 'تعديل المقال'],
    'key' => 'edit-post'
]);
```

### Bulk Permission Creation
```php
$guard = AccessControl()->guards()->show('web');

$permissions = [
    ['name' => ['en' => 'Create Post', 'ar' => 'إنشاء مقال'], 'key' => 'create-post'],
    ['name' => ['en' => 'Edit Post', 'ar' => 'تعديل مقال'], 'key' => 'edit-post'],
    ['name' => ['en' => 'Delete Post', 'ar' => 'حذف مقال'], 'key' => 'delete-post'],
    ['name' => ['en' => 'Publish Post', 'ar' => 'نشر مقال'], 'key' => 'publish-post']
];

foreach ($permissions as $permissionData) {
    $guard->permissions()->create($permissionData);
}
```

## Advanced Usage Examples

### Complex Queries
```php
// Get users with specific permissions
$users = User::whereHas('permissions', function($query) {
    $query->whereIn('key', ['edit-post', 'delete-post']);
})->get();

// Get roles with permissions from specific guard
$query = AccessControl()->roles()->list([], ['permissions'], false);
$roles = $query->whereHas('permissions.permissionGuard', function($query) {
    $query->where('key', 'web');
})->get();

// Get groups with specific roles
$query = AccessControl()->groups()->list([], ['roles'], false);
$groups = $query->whereHas('roles', function($query) {
    $query->where('key', 'admin');
})->get();
```

### Bulk Operations
```php
// Bulk assign roles to users
$userIds = [1, 2, 3, 4, 5];
$roleKeys = ['editor', 'moderator'];

$users = User::whereIn('id', $userIds)->get();
foreach ($users as $user) {
    $user->assignRole($roleKeys);
}

// Bulk create roles
$roleData = [
    ['name' => ['en' => 'User', 'ar' => 'مستخدم'], 'key' => 'user'],
    ['name' => ['en' => 'Moderator', 'ar' => 'مشرف'], 'key' => 'moderator'],
    ['name' => ['en' => 'Administrator', 'ar' => 'مدير'], 'key' => 'administrator']
];

foreach ($roleData as $data) {
    AccessControl()->roles()->create($data);
}
```

## Multilingual Examples

### Setting Application Locale
```php
// Set application locale
app()->setLocale('ar');

// Create multilingual entity
$role = AccessControl()->roles()->create([
    'name' => [
        'en' => 'Content Editor',
        'ar' => 'محرر المحتوى',
        'fr' => 'Éditeur de contenu'
    ],
    'key' => 'content-editor'
]);

// Access localized name
echo $role->name; // Returns: محرر المحتوى (when locale is 'ar')

// Get all translations
$translations = $role->getTranslations('name');
// Returns: ['en' => 'Content Editor', 'ar' => 'محرر المحتوى', 'fr' => 'Éditeur de contenu']
```

### Dynamic Language Switching
```php
// Switch language and get localized content
$role = AccessControl()->roles()->show('admin');

app()->setLocale('en');
echo $role->name; // "Administrator"

app()->setLocale('ar');
echo $role->name; // "مشرف"

app()->setLocale('fr');
echo $role->name; // "Administrateur"
```

## Controller Examples

### User Management Controller
```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function assignAccess(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'array',
            'permissions' => 'array',
            'groups' => 'array'
        ]);

        // Assign roles
        if ($request->has('roles')) {
            $user->assignRole($request->input('roles'));
        }

        // Assign permissions
        if ($request->has('permissions')) {
            $user->givePermissionTo($request->input('permissions'));
        }

        // Assign to groups
        if ($request->has('groups')) {
            $user->assignToGroups($request->input('groups'));
        }

        return response()->json([
            'message' => 'Access control updated successfully',
            'user' => $user->fresh(['roles', 'permissions', 'groups'])
        ]);
    }

    public function checkAccess(User $user)
    {
        return response()->json([
            'hasAdminRole' => $user->hasRole('admin'),
            'canEditPosts' => $user->hasPermissionTo('edit-post'),
            'inContentTeam' => $user->inGroup('content-team'),
            'permissions' => $user->getGroupedPermissionsByGuard()
        ]);
    }
}
```

### Role Management Controller
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        // Get all roles with permissions
        $roles = AccessControl()->roles()->list([], ['permissions']);

        // Get paginated roles
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
}
```

## Middleware Examples

### Custom Permission Middleware
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
```

### Route Protection
```php
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

## Blade Template Examples

### Permission Checks in Views
```blade
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
```

### Dynamic Menu Generation
```blade
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
            </ul>
        </div>
    </div>
</nav>
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

### 3. Handle Multilingual Content Properly
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

### 4. Use Middleware for Route Protection
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

## For More Examples

- See the comprehensive examples in `COMPREHENSIVE_EXAMPLES.md`
- Check the test files in `tests/Feature/PermissionPlusFeatureTest.php`
- Review the unit tests in `tests/Unit/HasTranslatableTest.php`
- Explore the package source code for additional implementation details
