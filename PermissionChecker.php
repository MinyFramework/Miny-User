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
    protected $user;

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function addPermission($name, iPermission $permission)
    {
        $this->permissions[$name] = $permission;
    }

    public function has($permission, User $user = NULL)
    {
        if (!isset($this->permissions[$permission])) {
            throw new \OutOfBoundsException('Permission not found: ' . $permission);
        }
        return $this->permissions[$permission]->userHasPermission($user ? : $this->user);
    }

    public function any(array $permissions, User $user = NULL)
    {
        foreach ($permissions as $permission) {
            if ($this->has($permission, $user)) {
                return true;
            }
        }
        return false;
    }

    public function all(array $permissions, User $user = NULL)
    {
        foreach ($permissions as $permission) {
            if (!$this->has($permission, $user)) {
                return false;
            }
        }
        return true;
    }

}