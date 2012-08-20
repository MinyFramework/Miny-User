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
    private $paths = array();
    private $permissions;
    private $redirects = array();
    private $default_redirect = false;

    public function __construct(PermissionChecker $permissions)
    {
        $this->permissions = $permissions;
    }

    public function getPermissionChecker()
    {
        return $this->permissions;
    }

    public function setDefaultRedirectPath($default_redirect)
    {
        $this->default_redirect = $default_redirect;
    }

    public function protectPath($path, $rule)
    {
        if (is_array($rule) && isset($rule['rule'])) {
            $this->paths[$path] = $rule['rule'];
            $this->redirects[$path] = isset($rule['path']) ? $rule['path'] : NULL;
        } else {
            $this->paths[$path] = $rule;
            $this->redirects[$path] = NULL;
        }
    }

    public function checkPath($path, User $user = NULL)
    {
        if (!isset($this->paths[$path])) {
            return true;
        }
        $rule = $this->paths[$path];

        if (is_string($rule)) {
            $return = $this->permissions->has($rule, $user);
        } elseif (is_array($rule)) {
            $return = $this->permissions->all($rule, $user);
        } elseif ($rule instanceof Closure) {
            $return = $rule($user);
        } else {
            throw new FirewallRuleException('Invalid rule applied to ' . $path);
        }

        if (!$return) {
            return $this->redirects[$path] ? : $this->default_redirect;
        }
        return true;
    }

}