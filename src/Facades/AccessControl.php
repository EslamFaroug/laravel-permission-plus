<?php

namespace EslamFaroug\PermissionPlus\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \EslamFaroug\PermissionPlus\Repositories\RoleRepository roles()
 * @method static \EslamFaroug\PermissionPlus\Repositories\GroupRepository groups()
 * @method static \EslamFaroug\PermissionPlus\Repositories\PermissionGuardRepository guards()
 */
class AccessControl extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'accesscontrol';
    }
}
