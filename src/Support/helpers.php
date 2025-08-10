<?php
/**
 * Get the AccessControlManager instance.
 *
 * @return \EslamFaroug\PermissionPlus\Services\AccessControlManager
 * @method static \EslamFaroug\PermissionPlus\Repositories\RoleRepository roles()
 * @method static \EslamFaroug\PermissionPlus\Repositories\GroupRepository groups()
 * @method static \EslamFaroug\PermissionPlus\Repositories\PermissionGuardRepository guards()
 */
if (!function_exists('AccessControl')) {
    function AccessControl()
    {
        return app('accesscontrol');
    }
}
