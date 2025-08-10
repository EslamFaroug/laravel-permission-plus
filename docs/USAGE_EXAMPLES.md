
# Laravel Permission Plus Usage Examples

## Assigning Roles, Permissions, and Groups
```php
$user->assignRole('editor');
$user->assignPermission('edit-post');
$user->assignToGroups('content-team');
```

## Checking Permissions, Roles, and Groups
```php
if ($user->hasPermissionTo('edit-post')) { /* ... */ }
if ($user->hasRole('admin')) { /* ... */ }
if ($user->inGroup('content-team')) { /* ... */ }
```

## Multilingual Support
```php
$role = Role::create(['name' => ['en'=>'Admin', 'ar'=>'مشرف'], 'key'=>'admin']);
app()->setLocale('ar');
echo $role->name; // مشرف
```

## Gate Integration
```php
if (Gate::allows('edit-post')) { /* ... */ }
```

## For more practical examples, see:
- tests/Feature/PermissionPlusFeatureTest.php
- tests/Unit/HasTranslatableTest.php
