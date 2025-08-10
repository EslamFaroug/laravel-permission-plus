<?php

namespace EslamFaroug\PermissionPlus\Repositories;

use EslamFaroug\PermissionPlus\Models\PermissionGuard;
use EslamFaroug\PermissionPlus\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class PermissionGuardRepository
{
    /**
     * Get a list of PermissionGuards with optional filter, relations, and data retrieval mode.
     *
     * @param array $filter
     * @param array $relations
     * @param bool $getData (true: get data, false: just query)
     * @return Collection|\Illuminate\Database\Eloquent\Builder
     */
    public function list(array $filter = [], array $relations = [], bool $getData = true)
    {
        $query = PermissionGuard::query();
        if (!empty($relations)) {
            $query->with($relations);
        }
        if (isset($filter['key'])) {
            $query->where('key', $filter['key']);
        }
        if (isset($filter['name'])) {
            $query->whereJsonContains('name', $filter['name']);
        }
        return $getData ? $query->get() : $query;
    }

    /**
     * Show a single PermissionGuard by id or key, with optional relations.
     *
     * @param int|string $idOrKey
     * @param array $relations
     * @return PermissionGuard|null
     */
    public function show($idOrKey, array $relations = []): ?PermissionGuard
    {
        $query = PermissionGuard::query();
        if (!empty($relations)) {
            $query->with($relations);
        }
        if (is_numeric($idOrKey)) {
            return $query->find($idOrKey);
        } else {
            return $query->where('key', $idOrKey)->first();
        }
    }

    /**
     * Create a new PermissionGuard (and optionally its permissions).
     */
    public function create(array $data): PermissionGuard
    {
        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);
        $guard = PermissionGuard::create($data);
        if (!empty($permissions)) {
            foreach ($permissions as $perm) {
                $guard->permissions()->create($perm);
            }
        }
        return $guard;
    }

    /**
     * Update a PermissionGuard (and optionally its permissions).
     */
    public function update(int $id, array $data): ?PermissionGuard
    {
        $guard = PermissionGuard::find($id);
        if (!$guard) return null;
        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);
        $guard->update($data);
        if (!empty($permissions)) {
            foreach ($permissions as $perm) {
                $guard->permissions()->updateOrCreate(
                    isset($perm['id']) ? ['id' => $perm['id']] : ['key' => $perm['key']],
                    $perm
                );
            }
        }
        return $guard->fresh('permissions');
    }

    /**
     * Delete a PermissionGuard and its permissions.
     */
    public function delete(int $id): bool
    {
        $guard = PermissionGuard::find($id);
        if (!$guard) return false;
        $guard->permissions()->delete();
        return $guard->delete();
    }
}
