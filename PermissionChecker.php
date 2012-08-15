<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENCE file.
 */

namespace Modules\User;

class PermissionChecker
{
    protected $permissions = array();

    public function addPermission($name, iPermission $permission)
    {
        $this->permissions[$name] = $permission;
    }

    public function has(User $user, $permission)
    {
        if (!isset($this->permissions[$permission])) {
            throw new \OutOfBoundsException('Permission not found: ' . $permission);
        }
        return $this->permissions[$permission]->userHasPermission($user);
    }

    public function any(User $user, array $permissions)
    {
        foreach ($permissions as $permission) {
            if ($this->has($user, $permission)) {
                return true;
            }
        }
        return false;
    }

    public function all(User $user, array $permissions)
    {
        foreach ($permissions as $permission) {
            if (!$this->has($user, $permission)) {
                return false;
            }
        }
        return true;
    }

}