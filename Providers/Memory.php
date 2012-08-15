<?php

/**
 * This file is part of the Miny framework.
 * (c) Dániel Buga <daniel@bugadani.hu>
 *
 * For licensing information see the LICENSE file.
 */

namespace Modules\User\Providers;

use Modules\User\UserProvider;
use Modules\User\User;
use OutOfBoundsException;

class Memory extends UserProvider
{
    private $users = array();

    public function __construct(array $users = array(), $classname = NULL)
    {
        parent::__construct($classname);
        foreach ($users as $user) {
            $this->save($this->create($user));
        }
    }

    public function get($key)
    {
        if (!isset($this->users[$key])) {
            throw new OutOfBoundsException('User not found: ' . $key);
        }
        return $this->users[$key];
    }

    public function has($key)
    {
        return isset($this->users[$key]);
    }

    public function remove(User $user)
    {
        $key = $user->name;
        if (isset($this->users[$key])) {
            unset($this->users[$key]);
            return true;
        }
    }

    public function add(User $user)
    {
        $key = $user->name;
        $this->users[$key] = $user;
        return true;
    }

}