<?php

namespace EslamFaroug\PermissionPlus\Repositories;

use EslamFaroug\PermissionPlus\Models\Group;
use Illuminate\Database\Eloquent\Collection;

class GroupRepository
{
	/**
	 * Get a list of groups with optional filter, relations, and data retrieval mode.
	 *
	 * @param array $filter
	 * @param array $relations
	 * @param bool $getData (true: get data, false: just query)
	 * @return Collection|\Illuminate\Database\Eloquent\Builder
	 */
	public function list(array $filter = [], array $relations = [], bool $getData = true)
	{
		$query = Group::query();
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
	 * Show a single group by id or key, with optional relations.
	 *
	 * @param int|string $idOrKey
	 * @param array $relations
	 * @return Group|null
	 */
	public function show($idOrKey, array $relations = []): ?Group
	{
		$query = Group::query();
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
	 * Create a new group (and optionally its roles).
	 */
	public function create(array $data): Group
	{
		$roles = $data['roles'] ?? [];
		unset($data['roles']);
		$group = Group::create($data);
		if (!empty($roles)) {
			$roleIds = $this->resolveRoleIds($roles);
			$group->roles()->sync($roleIds);
		}
		return $group;
	}

	/**
	 * Update a group (and optionally its roles).
	 */
	public function update(int $id, array $data): ?Group
	{
		$group = Group::find($id);
		if (!$group) return null;
		$roles = $data['roles'] ?? [];
		unset($data['roles']);
		$group->update($data);
		if (!empty($roles)) {
			$roleIds = $this->resolveRoleIds($roles);
			$group->roles()->sync($roleIds);
		}
		return $group->fresh('roles');
	}

	/**
	 * Delete a group and detach its roles.
	 */
	public function delete(int $id): bool
	{
		$group = Group::find($id);
		if (!$group) return false;
		$group->roles()->detach();
		return $group->delete();
	}

	/**
	 * Resolve role ids from array of ids or keys.
	 *
	 * @param array $roles
	 * @return array
	 */
	protected function resolveRoleIds(array $roles): array
	{
		if (empty($roles)) return [];
		if (collect($roles)->every(fn($r) => is_numeric($r))) {
			return $roles;
		}
		$model = config('permission-plus.models.role');
		return $model::whereIn('key', $roles)->pluck('id')->all();
	}
}
