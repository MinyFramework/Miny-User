<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENCE file.
 */

namespace Modules\User;

use Closure;
use Modules\User\Exceptions\FirewallRuleException;

class Firewall
{
    private $rules = array();
    private $paths = array();
    private $permissions;

    public function __construct(PermissionChecker $permissions)
    {
        $this->permissions = $permissions;
    }

    public function addRule($name, $rule)
    {
        $this->rules[$name] = $rule;
    }

    public function protectPath($path, $rule_name)
    {
        $this->paths[$path] = $rule_name;
    }

    public function checkPath($path, User $user)
    {
        if (!isset($this->paths[$path])) {
            return true;
        }
        $rule = $this->paths[$path];
        if (is_string($rule)) {
            return $this->permissions->has($user, $rule);
        } elseif (is_array($rule)) {
            return $this->permissions->all($user, $rule);
        } elseif ($rule instanceof Closure) {
            return $rule($user);
        }
        throw new FirewallRuleException('Invalid rule applied to ' . $path);
    }

}