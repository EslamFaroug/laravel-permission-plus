<?php

namespace EslamFaroug\PermissionPlus\Services;

use EslamFaroug\PermissionPlus\Repositories\RoleRepository;
use EslamFaroug\PermissionPlus\Repositories\GroupRepository;
use EslamFaroug\PermissionPlus\Repositories\PermissionGuardRepository;

class AccessControlManager
{
    protected $roles;
    protected $groups;
    protected $guards;

    public function __construct()
    {
        $this->roles = new RoleRepository();
        $this->groups = new GroupRepository();
        $this->guards = new PermissionGuardRepository();
    }

    public function roles() : RoleRepository
    {
        return $this->roles;
    }

    public function groups() : GroupRepository
    {
        return $this->groups;
    }

    public function guards() : PermissionGuardRepository
    {
        return $this->guards;
    }   
}
