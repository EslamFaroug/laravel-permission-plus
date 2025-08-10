<?php

namespace EslamFaroug\PermissionPlus\Repositories;

use EslamFaroug\PermissionPlus\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository
{
	/**
	 * Get a list of roles with optional filter, relations, and data retrieval mode.
	 *
	 * @param array $filter
	 * @param array $relations
	 * @param bool $getData (true: get data, false: just query)
	 * @return Collection|\Illuminate\Database\Eloquent\Builder
	 */
	public function list(array $filter = [], array $relations = [], bool $getData = true)
	{
		$query = Role::query();
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
	 * Show a single role by id or key, with optional relations.
	 *
	 * @param int|string $idOrKey
	 * @param array $relations
	 * @return Role|null
	 */
	public function show($idOrKey, array $relations = []): ?Role
	{
		$query = Role::query();
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
	 * Create a new role (and optionally its permissions).
	 */
	public function create(array $data): Role
	{
		$permissions = $data['permissions'] ?? [];
		unset($data['permissions']);
		$role = Role::create($data);
		if (!empty($permissions)) {
			$permissionIds = $this->resolvePermissionIds($permissions);
			$role->permissions()->sync($permissionIds);
		}
		return $role;
	}

	/**
	 * Update a role (and optionally its permissions).
	 */
	public function update(int $id, array $data): ?Role
	{
		$role = Role::find($id);
		if (!$role) return null;
		$permissions = $data['permissions'] ?? [];
		unset($data['permissions']);
		$role->update($data);
		if (!empty($permissions)) {
			$permissionIds = $this->resolvePermissionIds($permissions);
			$role->permissions()->sync($permissionIds);
		}
		return $role->fresh('permissions');
	}
	/**
	 * Resolve permission ids from array of ids or keys.
	 *
	 * @param array $permissions
	 * @return array
	 */
	protected function resolvePermissionIds(array $permissions): array
	{
		if (empty($permissions)) return [];
		// If all numeric, return as is
		if (collect($permissions)->every(fn($p) => is_numeric($p))) {
			return $permissions;
		}
		// Otherwise, treat as keys
		$model = config('permission-plus.models.permission');
		return $model::whereIn('key', $permissions)->pluck('id')->all();
	}

	/**
	 * Delete a role and detach its permissions.
	 */
	public function delete(int $id): bool
	{
		$role = Role::find($id);
		if (!$role) return false;
		$role->permissions()->detach();
		return $role->delete();
	}
}
