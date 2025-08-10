<?php
namespace EslamFaroug\PermissionPlus\Traits;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Trait to add group, role, and permission management to any model.
 */
trait HasAccessControl
{
    // Groups
    public function groups(): MorphToMany
    {
        return $this->morphToMany(
            config('permission-plus.models.group', Group::class),
            'groupable',
            config('permission-plus.tables.groupables', 'groupables'),
            'groupable_id',
            'group_id'
        )->withTimestamps();
    }


    public function assignToGroups(...$groups)
    {
        $groupIds = $this->resolveIdsOrKeys($groups, config('permission-plus.models.group'));
        $this->groups()->syncWithoutDetaching($groupIds);
        return $this;
    }


    public function removeFromGroups(...$groups)
    {
        $groupIds = $this->resolveIdsOrKeys($groups, config('permission-plus.models.group'));
        $this->groups()->detach($groupIds);
        return $this;
    }

    // Roles
    public function roles(): MorphToMany
    {
        return $this->morphToMany(
            config('permission-plus.models.role', Role::class),
            'assignable',
            config('permission-plus.tables.permission_assignments', 'permission_assignments'),
            'assignable_id',
            'role_id'
        )->withTimestamps();
    }


    public function assignRole(...$roles)
    {
        $roleIds = $this->resolveIdsOrKeys($roles, config('permission-plus.models.role'));
        $this->roles()->syncWithoutDetaching($roleIds);
        return $this;
    }


    public function removeRole(...$roles)
    {
        $roleIds = $this->resolveIdsOrKeys($roles, config('permission-plus.models.role'));
        $this->roles()->detach($roleIds);
        return $this;
    }

    // Permissions
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(
            config('permission-plus.models.permission', Permission::class),
            'assignable',
            config('permission-plus.tables.permission_assignments', 'permission_assignments'),
            'assignable_id',
            'permission_id'
        )->withTimestamps();
    }


    public function givePermissionTo(...$permissions)
    {
        $permissionIds = $this->resolveIdsOrKeys($permissions, config('permission-plus.models.permission'));
        $this->permissions()->syncWithoutDetaching($permissionIds);
        return $this;
    }


    public function revokePermissionTo(...$permissions)
    {
        $permissionIds = $this->resolveIdsOrKeys($permissions, config('permission-plus.models.permission'));
        $this->permissions()->detach($permissionIds);
        return $this;
    }
    /**
     * Helper to resolve array of ids or keys to ids.
     */
    protected function resolveIdsOrKeys($items, $modelClass)
    {
        $flat = collect($items)->flatten();
        if ($flat->every(fn($v) => is_numeric($v))) {
            return $flat->all();
        }
        if ($flat->every(fn($v) => is_object($v) && isset($v->id))) {
            return $flat->pluck('id')->all();
        }
        // Assume keys
        return $modelClass::whereIn('key', $flat)->pluck('id')->all();
    }

    // Checks
    public function hasRole($roleKey): bool
    {
        return $this->roles()->where('key', $roleKey)->exists();
    }

    public function hasPermissionTo($permissionKey): bool
    {
        return $this->permissions()->where('key', $permissionKey)->exists();
    }

    public function inGroup($groupKey): bool
    {
        return $this->groups()->where('key', $groupKey)->exists();
    }

     /**
     * Get permissions grouped by PermissionGuard if permissions relation is loaded.
     *
     * @return array
     */
    public function getGroupedPermissionsByGuard(): array
    {
        if (!$this->relationLoaded('permissions')) {
            return [];
        }
        $permissions = $this->permissions;
        $grouped = $permissions->groupBy(function ($permission) {
            return $permission->permissionGuard->key ?? null;
        });
        $result = [];
        foreach ($grouped as $guardKey => $perms) {
            if (!$perms->count()) continue;
            $guard = $perms->first()->permissionGuard;
            $result[] = [
                'key' => $guard->key ?? null,
                'name' => $guard->name['ar'] ?? $guard->name['en'] ?? null,
                'permissions' => $perms->map(function ($perm) {
                    return [
                        'key' => $perm->key,
                        'name' => $perm->name['ar'] ?? $perm->name['en'] ?? null,
                    ];
                })->values()->all(),
            ];
        }
        return $result;
    }
}
