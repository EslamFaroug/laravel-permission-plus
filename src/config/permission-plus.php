
<?php

return [
    'models' => [
        'permission' => \EslamFaroug\PermissionPlus\Models\Permission::class,
        'role' => \EslamFaroug\PermissionPlus\Models\Role::class,
        'permission_guard' => \EslamFaroug\PermissionPlus\Models\PermissionGuard::class,
        'group' => \EslamFaroug\PermissionPlus\Models\Group::class,
    ],
    'tables' => [
        'permission_guards' => 'permission_guards',
        'permissions' => 'permissions',
        'roles' => 'roles',
        'groups' => 'groups',
        'permission_assignments' => 'permission_assignments',
        'role_assignments' => 'role_assignments',
        'groupables' => 'groupables',
    ],
    'column_names' => [
        'model_morph_key' => 'model_id',
        'group_morph_key' => 'groupable_id',
    ],

        // Supported languages for translatable fields
    'languages' => ['en', 'ar', 'fr'],
];
