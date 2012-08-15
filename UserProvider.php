<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\User;

abstract class UserProvider
{
    private $class;

    public function __construct($classname = NULL)
    {
        $this->class = $classname ? : 'Modules\User\User';
    }

    public function create(array $user_data = array())
    {
        return new $this->class($user_data);
    }

    public abstract function add(User $user);
    public abstract function remove(User $user);
    public abstract function get($key);
    public abstract function has($key);
}