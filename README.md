# Laravel Permission Plus

<p align="center">
	<img src="logo.png" alt="Laravel Permission Plus Logo" width="180" />
</p>

**Author:** [Eslam Faroug](mailto:eslamfaroug3@gmail.com)

---

## 📚 Table of Contents

- [🚀 Introduction](#-introduction)
- [✨ Key Features](#-key-features)
- [📖 Documentation](#-documentation)
- [⚙️ Installation](#️-installation)
- [🔧 Core Components](#-core-components)
- [🎯 Quick Start Guide](#-quick-start-guide)
- [📋 Basic Usage Examples](#-basic-usage-examples)
- [🔄 CRUD Operations](#-crud-operations)
- [🌐 Advanced Features](#-advanced-features)
- [🔗 Customization & Integration](#-customization--integration)
- [📝 Contributing](#-contributing)
- [📄 License](#-license)

---

## 🚀 Introduction

**Laravel Permission Plus** is an advanced, flexible, and multilingual package for managing permissions, roles, and groups in Laravel applications. It provides a comprehensive RBAC (Role-Based Access Control) system with support for:

- **Multilingual content** (JSON columns for Arabic, English, French, etc.)
- **Polymorphic relationships** (assign to any Eloquent model)
- **Group-based access control** (organize users into teams)
- **Automatic caching** (performance optimization)
- **Multi-guard support** (web, API, admin, etc.)

Perfect for applications requiring sophisticated access control with internationalization support.

---

## ✨ Key Features

| Feature | Description |
|---------|-------------|
| 🔐 **Advanced RBAC** | Complete permission, role, and group management |
| 🌍 **Multilingual** | JSON columns for name/description in multiple languages |
| 🔗 **Polymorphic** | Assign permissions to any model (User, Employee, Client) |
| ⚡ **Auto-caching** | Intelligent cache management with auto-clear |
| 🎯 **Simple API** | Easy-to-use Traits, Facades, and Helper functions |
| 🛡️ **Multi-guard** | Support for different authentication contexts |
| 🔧 **Extensible** | Fully customizable tables, models, and relationships |
| 📱 **Modern** | Built for Laravel 8+ with modern PHP practices |

---

## 📖 Documentation

This package includes comprehensive documentation organized by complexity level:

| Document | Purpose | Best For |
|----------|---------|----------|
| **[📋 Usage Examples](USAGE_EXAMPLES.md)** | Basic operations and common patterns | **Beginners** - Getting started |
| **[📚 Comprehensive Examples](COMPREHENSIVE_EXAMPLES.md)** | Advanced features and complex scenarios | **Advanced users** - Mastering the package |
| **[📖 README.md](README.md)** | Overview and CRUD operations | **Reference** - Quick lookups |

---

## ⚙️ Installation

### 1. Install Package
```bash
composer require eslamfaroug/laravel-permission-plus
```

### 2. Publish Configuration
```bash
php artisan vendor:publish --provider="EslamFaroug\PermissionPlus\Providers\PermissionServiceProvider" --tag="permission-plus-config"
php artisan vendor:publish --provider="EslamFaroug\PermissionPlus\Providers\PermissionServiceProvider" --tag="permission-plus-migrations"
```

### 3. Run Migrations
```bash
php artisan migrate
```

### 4. Verify Installation
```bash
composer test
# or
./vendor/bin/phpunit
```

---

## 🔧 Core Components

### Models
- **`Permission`** - Individual access rights
- **`Role`** - Collections of permissions
- **`Group`** - Collections of roles and users
- **`PermissionGuard`** - Permission containers by context

### Traits
- **`HasAccessControl`** - Assign/check roles, permissions, groups
- **`HasTranslatable`** - Automatic multilingual field handling

### Services
- **`AccessControlManager`** - Central service for all operations

### API Access
- **Helper Function**: `AccessControl()->roles()->list()`
- **Facade**: `AccessControl::roles()->list()`

---

## 🎯 Quick Start Guide

### Step 1: Add Trait to User Model
```php
<?php

namespace App\Models;

use EslamFaroug\PermissionPlus\Traits\HasAccessControl;

class User extends Authenticatable
{
    use HasAccessControl;
    
    // ... rest of your model
}
```

### Step 2: Basic Usage
```php
// Assign roles and permissions
$user->assignRole('editor');
$user->givePermissionTo('edit-posts');

// Check access
if ($user->hasRole('admin')) {
    // Admin actions
}

if ($user->hasPermissionTo('delete-posts')) {
    // Delete post action
}
```

### Step 3: Explore Documentation
- **[📋 Usage Examples](USAGE_EXAMPLES.md)** - Start here for basic patterns
- **[📚 Comprehensive Examples](COMPREHENSIVE_EXAMPLES.md)** - Advanced features

---

## 📋 Basic Usage Examples

> 💡 **For detailed examples and common patterns, see [Usage Examples](USAGE_EXAMPLES.md)**

### User Access Control
```php
// Assign roles and permissions
$user->assignRole('editor');
$user->givePermissionTo('edit-articles');
$user->assignToGroups('content-team');

// Check permissions
$user->hasRole('admin');                    // true/false
$user->hasPermissionTo('delete-posts');     // true/false
$user->inGroup('content-team');            // true/false

// Get all permissions
$permissions = $user->getAllPermissions();
$roles = $user->getAllRoles();
$groups = $user->getAllGroups();
```

### Role and Permission Management
```php
// Create roles with permissions
$role = AccessControl()->roles()->create([
    'name' => ['en' => 'Editor', 'ar' => 'محرر'],
    'key' => 'editor',
    'permissions' => ['create-post', 'edit-post', 'delete-post']
]);

// Create permissions
$permission = AccessControl()->guards()->create([
    'name' => ['en' => 'Web Guard', 'ar' => 'حارس الويب'],
    'key' => 'web',
    'permissions' => [
        ['name' => ['en' => 'View Posts', 'ar' => 'عرض المقالات'], 'key' => 'view-posts']
    ]
]);
```

---

## 🔄 CRUD Operations

The package provides a unified API for all CRUD operations through the `AccessControl` helper.

### 🔍 List Operations
```php
// List with filters and relations
$roles = AccessControl()->roles()->list(
    ['key' => 'admin'],           // filters
    ['permissions', 'groups'],     // relations
    true                          // get data (false returns query builder)
);

// Get query builder for custom operations
$query = AccessControl()->roles()->list([], [], false);
$paginatedRoles = $query->paginate(15);
```

### ➕ Create Operations
```php
// Create role with permissions
$role = AccessControl()->roles()->create([
    'name' => ['en' => 'Content Manager', 'ar' => 'مدير المحتوى'],
    'key' => 'content-manager',
    'permissions' => ['create-post', 'edit-post', 'delete-post']
]);

// Create group with roles
$group = AccessControl()->groups()->create([
    'name' => ['en' => 'Content Team', 'ar' => 'فريق المحتوى'],
    'key' => 'content-team',
    'roles' => ['editor', 'reviewer', 'publisher']
]);
```

### ✏️ Update Operations
```php
// Update with new data
$role = AccessControl()->roles()->update(1, [
    'name' => ['en' => 'Senior Editor', 'ar' => 'محرر أول'],
    'permissions' => ['manage-content', 'approve-posts']
]);

// Direct model update
$role = AccessControl()->roles()->show(1);
$role->name = ['en' => 'Updated Name', 'ar' => 'اسم محدث'];
$role->save();
```

### 🗑️ Delete Operations
```php
// Delete by ID
$deleted = AccessControl()->roles()->delete(1);

// Delete model
$role = AccessControl()->roles()->show(1);
$deleted = $role->delete();
```

---

## 🌐 Advanced Features

> 💡 **For comprehensive examples and advanced patterns, see [Comprehensive Examples](COMPREHENSIVE_EXAMPLES.md)**

### Multilingual Support
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
```

### Complex Queries
```php
// Get roles with specific permissions
$query = AccessControl()->roles()->list([], ['permissions'], false);
$rolesWithEditPermission = $query->whereHas('permissions', function($q) {
    $q->where('key', 'edit-post');
})->get();

// Get groups with admin roles
$query = AccessControl()->groups()->list([], ['roles'], false);
$groupsWithAdminRole = $query->whereHas('roles', function($q) {
    $q->where('key', 'admin');
})->get();
```

### Bulk Operations
```php
// Bulk create roles
$roleData = [
    ['name' => ['en' => 'User', 'ar' => 'مستخدم'], 'key' => 'user'],
    ['name' => ['en' => 'Moderator', 'ar' => 'مشرف'], 'key' => 'moderator'],
    ['name' => ['en' => 'Administrator', 'ar' => 'مدير'], 'key' => 'administrator']
];

foreach ($roleData as $data) {
    AccessControl()->roles()->create($data);
}

// Bulk assign permissions
$role = AccessControl()->roles()->show('admin');
$permissions = ['manage-users', 'manage-roles', 'system-settings'];
$role->permissions()->sync($permissions);
```

---

## 🔗 Customization & Integration

### Configuration
```php
// config/permission-plus.php
return [
    'models' => [
        'permission' => App\Models\CustomPermission::class,
        'role' => App\Models\CustomRole::class,
        'group' => App\Models\CustomGroup::class,
        'guard' => App\Models\CustomGuard::class,
    ],
    'languages' => ['en', 'ar', 'fr', 'de'], // supported languages
];
```

### Middleware Integration
```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    // Permission-based routes
    Route::middleware(['permission:edit-post'])->group(function () {
        Route::get('/posts/{post}/edit', [PostController::class, 'edit']);
        Route::put('/posts/{post}', [PostController::class, 'update']);
    });

    // Role-based routes
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('users', UserController::class);
    });
});
```

### Blade Templates
```php
{{-- Check permissions in views --}}
@if(auth()->user()->hasPermissionTo('edit-posts'))
    <a href="{{ route('posts.edit', $post) }}">Edit Post</a>
@endif

@if(auth()->user()->hasRole('admin'))
    <div class="admin-panel">
        Admin controls here
    </div>
@endif
```

---

## 📝 Contributing

### Before Contributing
1. **Check existing documentation**:
   - **[Usage Examples](USAGE_EXAMPLES.md)** - Common patterns and solutions
   - **[Comprehensive Examples](COMPREHENSIVE_EXAMPLES.md)** - Advanced usage and edge cases

2. **Review the codebase** to understand the architecture

3. **Test your changes** thoroughly

### Reporting Issues
- Provide detailed error messages
- Include code examples
- Check if the issue is covered in the documentation

---

## 📄 License

**MIT License** © Eslam Faroug

---

## 🆘 Need Help?

- **📋 [Usage Examples](USAGE_EXAMPLES.md)** - Start here for basic usage
- **📚 [Comprehensive Examples](COMPREHENSIVE_EXAMPLES.md)** - Advanced features and patterns
- **🐛 [Report Issues](https://github.com/eslamfaroug/laravel-permission-plus/issues)** - Bug reports and feature requests
- **📧 [Contact Author](mailto:eslamfaroug3@gmail.com)** - Direct support

---

<div align="center">
  <sub>Built with ❤️ for the Laravel community</sub>
</div>
